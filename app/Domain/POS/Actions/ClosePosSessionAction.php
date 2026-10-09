<?php

namespace App\Domain\POS\Actions;

use App\Domain\Audit\Models\AuditLog;
use App\Domain\POS\Models\PosSession;
use Illuminate\Support\Facades\DB;

class ClosePosSessionAction
{
    public function __construct(
        protected GenerateZReportAction $generateZReportAction
    ) {}

    public function execute(
        string $posSessionId,
        float $closingCashDeclared,
        ?string $notes = null
    ): PosSession {
        return DB::transaction(function () use ($posSessionId, $closingCashDeclared, $notes) {
            $session = PosSession::where('id', $posSessionId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $session->isOpen()) {
                throw new \InvalidArgumentException("Session {$session->session_number} is already closed.");
            }

            $difference = round($closingCashDeclared - (float) $session->closing_cash_calculated, 2);

            $session->closing_cash_declared = $closingCashDeclared;
            $session->cash_difference = $difference;
            $session->status = 'closed';
            $session->closed_at = now();
            if ($notes) {
                $session->notes = $session->notes ? "{$session->notes}\n{$notes}" : $notes;
            }
            $session->save();

            // Generate End-of-Day Z-Report
            $this->generateZReportAction->execute($session->id);

            // Audit logging
            AuditLog::create([
                'tenant_id' => $session->tenant_id,
                'user_id' => $session->cashier_id,
                'action' => 'pos.session_closed',
                'entity_type' => PosSession::class,
                'entity_id' => $session->id,
                'old_values' => ['status' => 'open', 'closing_cash_calculated' => $session->closing_cash_calculated],
                'new_values' => [
                    'status' => 'closed',
                    'closing_cash_declared' => $closingCashDeclared,
                    'cash_difference' => $difference,
                ],
                'created_at' => now(),
            ]);

            return $session;
        });
    }
}
