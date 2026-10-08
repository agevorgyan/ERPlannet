<?php

namespace App\Domain\Delivery\Actions;

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Delivery\Models\CodSettlement;
use App\Domain\POS\Models\PosCashMovement;
use App\Domain\POS\Models\PosSession;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SettleCourierCodAction
{
    /**
     * Step 1: Courier submits cash to branch/cashier desk
     */
    public function submitCash(string $codSettlementId, float $submittedAmount): CodSettlement
    {
        return DB::transaction(function () use ($codSettlementId, $submittedAmount) {
            $settlement = CodSettlement::lockForUpdate()->findOrFail($codSettlementId);

            if (!in_array($settlement->status, ['expected', 'collected', 'courier_holding'], true)) {
                throw new InvalidArgumentException("Cannot submit COD from status {$settlement->status}.");
            }

            $settlement->status = 'submitted';
            $settlement->submitted_amount = $submittedAmount;
            $settlement->save();

            AuditLog::create([
                'tenant_id' => $settlement->tenant_id,
                'user_id' => auth()->id(),
                'action' => 'cod.submitted',
                'entity_type' => CodSettlement::class,
                'entity_id' => $settlement->id,
                'new_values' => [
                    'submitted_amount' => $submittedAmount,
                    'status' => 'submitted',
                ],
                'created_at' => now(),
            ]);

            return $settlement;
        });
    }

    /**
     * Step 2: Cashier counts and verifies cash, recording any discrepancy
     */
    public function verifyCash(
        string $codSettlementId,
        float $verifiedAmount,
        ?string $discrepancyReason = null,
        ?string $cashierId = null
    ): CodSettlement {
        return DB::transaction(function () use ($codSettlementId, $verifiedAmount, $discrepancyReason, $cashierId) {
            $settlement = CodSettlement::lockForUpdate()->findOrFail($codSettlementId);

            $expected = (float) $settlement->expected_amount;
            $diff = round($verifiedAmount - $expected, 2);

            $settlement->status = 'verified';
            $settlement->verified_amount = $verifiedAmount;
            $settlement->discrepancy_amount = $diff;
            $settlement->cashier_id = $cashierId ?? auth()->id();
            if ($diff !== 0.0 || $discrepancyReason) {
                $settlement->discrepancy_reason = $discrepancyReason ?: "Verified: {$verifiedAmount} AMD (Difference: {$diff} AMD)";
            }
            $settlement->save();

            AuditLog::create([
                'tenant_id' => $settlement->tenant_id,
                'user_id' => $settlement->cashier_id,
                'action' => 'cod.verified',
                'entity_type' => CodSettlement::class,
                'entity_id' => $settlement->id,
                'new_values' => [
                    'expected_amount' => $expected,
                    'verified_amount' => $verifiedAmount,
                    'discrepancy_amount' => $diff,
                    'reason' => $settlement->discrepancy_reason,
                ],
                'created_at' => now(),
            ]);

            return $settlement;
        });
    }

    /**
     * Step 3: Cashier deposits into cash register or company treasury
     */
    public function settleCash(
        string $codSettlementId,
        ?string $posSessionId = null,
        ?string $cashierId = null
    ): CodSettlement {
        return DB::transaction(function () use ($codSettlementId, $posSessionId, $cashierId) {
            $settlement = CodSettlement::lockForUpdate()->findOrFail($codSettlementId);

            $cashier = $cashierId ?? auth()->id();
            $settledAmt = (float) ($settlement->verified_amount ?? $settlement->collected_amount ?? $settlement->expected_amount);

            $settlement->status = 'settled';
            $settlement->settled_at = now();
            $settlement->cashier_id = $cashier;
            $settlement->save();

            // If POS session provided, deposit cash into drawer
            if ($posSessionId) {
                $session = PosSession::lockForUpdate()->find($posSessionId);
                if ($session && $session->isOpen()) {
                    $session->closing_cash_calculated = (float) $session->closing_cash_calculated + $settledAmt;
                    $session->save();

                    PosCashMovement::create([
                        'tenant_id' => $settlement->tenant_id,
                        'pos_session_id' => $session->id,
                        'user_id' => $cashier,
                        'type' => 'cash_in',
                        'amount' => $settledAmt,
                        'reason' => "COD Settlement for Shipment #{$settlement->shipment?->shipment_number}",
                        'created_at' => now(),
                    ]);
                }
            }

            AuditLog::create([
                'tenant_id' => $settlement->tenant_id,
                'user_id' => $cashier,
                'action' => 'cod.settled',
                'entity_type' => CodSettlement::class,
                'entity_id' => $settlement->id,
                'new_values' => [
                    'status' => 'settled',
                    'settled_amount' => $settledAmt,
                    'pos_session_id' => $posSessionId,
                ],
                'created_at' => now(),
            ]);

            return $settlement;
        });
    }
}
