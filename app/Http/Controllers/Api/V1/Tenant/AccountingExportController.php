<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Domain\Integration\Drivers\ArmenianSoftwareExportDriver;
use App\Domain\Integration\Models\TenantIntegration;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountingExportController extends Controller
{
    public function __construct(
        protected ArmenianSoftwareExportDriver $asExportDriver
    ) {}

    public function exportInvoices(Request $request): Response
    {
        $tenantId = $request->header('X-Tenant-ID') ?: $request->user()?->tenant_id;

        $dummyIntegration = new TenantIntegration(['tenant_id' => $tenantId, 'provider' => 'armenian_software']);
        $result = $this->asExportDriver->exportInvoices($dummyIntegration, $request->all());

        return response($result['content'], 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$result['filename']}\"",
            'X-Export-Count' => $result['count'],
        ]);
    }

    public function exportInventory(Request $request): Response
    {
        $tenantId = $request->header('X-Tenant-ID') ?: $request->user()?->tenant_id;

        $dummyIntegration = new TenantIntegration(['tenant_id' => $tenantId, 'provider' => 'armenian_software']);
        $result = $this->asExportDriver->exportInventory($dummyIntegration, $request->all());

        return response($result['content'], 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$result['filename']}\"",
            'X-Export-Count' => $result['count'],
        ]);
    }
}
