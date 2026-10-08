<?php

namespace App\Domain\POS\Actions;

use App\Domain\POS\Models\PosCashMovement;
use App\Domain\POS\Models\PosSession;
use Illuminate\Support\Facades\DB;

class RecordPosCashMovementAction
{
    public function execute(
        string $posSessionId,
        string $userId,
        string $type,
        float $amount,
        string $reason
    ): PosCashMovement {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Cash movement amount must be greater than zero.');
        }

        if (!in_array($type, ['cash_in', 'cash_out'], true)) {
            throw new \InvalidArgumentException("Invalid cash movement type: {$type}. Must be 'cash_in' or 'cash_out'.");
        }

        return DB::transaction(function () use ($posSessionId, $userId, $type, $amount, $reason) {
            $session = PosSession::where('id', $posSessionId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$session->isOpen()) {
                throw new \InvalidArgumentException("Cannot record cash movement on a closed session ({$session->session_number}).");
            }

            if ($type === 'cash_out' && (float) $session->closing_cash_calculated < $amount) {
                throw new \InvalidArgumentException("Insufficient cash in drawer for withdrawal: calculated balance is {$session->closing_cash_calculated} AMD.");
            }

            $movement = PosCashMovement::create([
                'tenant_id' => $session->tenant_id,
                'pos_session_id' => $session->id,
                'user_id' => $userId,
                'type' => $type,
                'amount' => $amount,
                'reason' => $reason,
                'created_at' => now(),
            ]);

            if ($type === 'cash_in') {
                $session->closing_cash_calculated = (float) $session->closing_cash_calculated + $amount;
            } else {
                $session->closing_cash_calculated = (float) $session->closing_cash_calculated - $amount;
            }
            $session->save();

            return $movement;
        });
    }
}
