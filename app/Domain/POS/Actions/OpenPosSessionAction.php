<?php

namespace App\Domain\POS\Actions;

use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Domain\POS\Models\PosSession;
use App\Domain\POS\Models\PosTerminal;
use App\Domain\POS\Services\PosSessionNumberGenerator;
use App\Infrastructure\MultiTenancy\TenantContext;

class OpenPosSessionAction
{
    public function __construct(
        protected EntitlementManagerInterface $entitlements,
        protected PosSessionNumberGenerator $numberGenerator,
        protected TenantContext $tenantContext
    ) {}

    public function execute(
        string $posTerminalId,
        string $cashierId,
        float $openingCash = 0.0,
        ?string $notes = null
    ): PosSession {
        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            throw new \RuntimeException('Tenant context not set.');
        }

        $this->entitlements->assertCan('feature.pos');

        $terminal = PosTerminal::findOrFail($posTerminalId);
        if (! $terminal->is_active) {
            throw new \InvalidArgumentException("POS Terminal {$terminal->code} is inactive.");
        }

        $activeSession = PosSession::where('pos_terminal_id', $terminal->id)
            ->where('status', 'open')
            ->first();

        if ($activeSession) {
            throw new \InvalidArgumentException("POS Terminal already has an open session: {$activeSession->session_number}.");
        }

        $sessionNumber = $this->numberGenerator->generate($tenant);

        return PosSession::create([
            'tenant_id' => $tenant->id,
            'pos_terminal_id' => $terminal->id,
            'cashier_id' => $cashierId,
            'session_number' => $sessionNumber,
            'opening_cash' => $openingCash,
            'closing_cash_calculated' => $openingCash,
            'status' => 'open',
            'opened_at' => now(),
            'notes' => $notes,
        ]);
    }
}
