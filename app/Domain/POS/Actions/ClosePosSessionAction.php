<?php

namespace App\Domain\POS\Actions;

use App\Domain\POS\Models\PosSession;
use Illuminate\Support\Facades\DB;

class ClosePosSessionAction
{
    public function execute(
        string $posSessionId,
        float $closingCashDeclared,
        ?string $notes = null
    ): PosSession {
        return DB::transaction(function () use ($posSessionId, $closingCashDeclared, $notes) {
            $session = PosSession::where('id', $posSessionId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$session->isOpen()) {
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

            return $session;
        });
    }
}
