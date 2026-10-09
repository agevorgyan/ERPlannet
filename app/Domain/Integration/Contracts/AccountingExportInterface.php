<?php

declare(strict_types=1);

namespace App\Domain\Integration\Contracts;

use App\Domain\Integration\Models\TenantIntegration;

interface AccountingExportInterface extends IntegrationDriverInterface
{
    /**
     * Export sales invoices in standardized interchange format (XML/JSON).
     *
     * @param  array<string, mixed>  $filters
     * @return array{format: string, filename: string, content: string, count: int}
     */
    public function exportInvoices(TenantIntegration $integration, array $filters = []): array;

    /**
     * Export inventory balances and movements in standardized interchange format.
     *
     * @param  array<string, mixed>  $filters
     * @return array{format: string, filename: string, content: string, count: int}
     */
    public function exportInventory(TenantIntegration $integration, array $filters = []): array;
}
