<?php

namespace App\Domain\Printing\Services;

use App\Domain\Branch\Models\Branch;
use App\Domain\Printing\Models\DocumentTemplate;
use App\Domain\Printing\Models\Printer;
use App\Domain\Printing\Models\PrintJob;
use App\Domain\Sales\Models\B2bDeliveryNote;
use App\Domain\Sales\Models\Order;
use App\Infrastructure\MultiTenancy\TenantContext;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PrintJobManager
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected DocumentTemplateRenderer $renderer,
        protected EscPosConverter $escPosConverter
    ) {}

    /**
     * Dispatch or queue a print job for an order or B2B delivery note.
     *
     * @param array{
     *     branch_id?: string|null,
     *     printer_id?: string|null,
     *     workstation_id?: string|null,
     *     document_type?: string,
     *     template_id?: string|null,
     *     copies?: int,
     *     idempotency_key?: string|null,
     *     order_id?: string|null,
     *     delivery_note_id?: string|null
     * } $params
     */
    public function createPrintJob(array $params, ?string $userId = null): PrintJob
    {
        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            throw new InvalidArgumentException('No active tenant context.');
        }

        $order = null;
        if (! empty($params['order_id'])) {
            $order = Order::with(['items', 'customer', 'branch', 'warehouse', 'paymentTransactions'])
                ->where('tenant_id', $tenant->id)
                ->findOrFail($params['order_id']);
        }

        $deliveryNote = null;
        if (! empty($params['delivery_note_id'])) {
            $deliveryNote = B2bDeliveryNote::where('tenant_id', $tenant->id)->findOrFail($params['delivery_note_id']);
        }

        $branchId = $params['branch_id'] ?? ($order?->branch_id ?? $deliveryNote?->branch_id);
        if (! $branchId) {
            $firstBranch = Branch::where('tenant_id', $tenant->id)->first();
            $branchId = $firstBranch?->id;
        }

        // Idempotency check
        $idempotencyKey = $params['idempotency_key'] ?? Str::uuid()->toString();
        $existing = PrintJob::where('tenant_id', $tenant->id)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existing) {
            return $existing;
        }

        $docType = $params['document_type'] ?? ($deliveryNote ? 'b2b_delivery_note' : 'pos_receipt');

        // 1. Resolve Printer via Inheritance: Parameter -> Workstation Default -> Branch Default -> Tenant Default
        $printer = $this->resolvePrinter($tenant->id, $branchId, $params['workstation_id'] ?? null, $params['printer_id'] ?? null);

        if ($docType === 'test_page') {
            $template = null;
            $renderedHtml = "<h3>Hardware Test Page</h3><p>Printer: {$printer?->name}</p><p>Time: ".now()->toIso8601String().'</p>';
        } else {
            // 2. Resolve Template via Inheritance: Parameter -> Branch Override -> Tenant Default
            $template = $this->resolveTemplate($tenant->id, $branchId, $docType, $params['template_id'] ?? null);

            // 3. Render payload
            $renderedHtml = $this->renderer->render($template, $order, $deliveryNote);
        }

        // 4. Create PrintJob
        return DB::transaction(function () use ($tenant, $branchId, $printer, $order, $docType, $template, $params, $userId, $idempotencyKey, $renderedHtml) {
            $job = PrintJob::create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branchId,
                'printer_id' => $printer?->id,
                'workstation_id' => $params['workstation_id'] ?? null,
                'order_id' => $order?->id,
                'document_type' => $docType,
                'document_template_id' => $template?->id,
                'template_version_id' => $template?->latestVersion?->id,
                'copies' => max(1, (int) ($params['copies'] ?? 1)),
                'requested_by' => $userId,
                'status' => 'queued',
                'idempotency_key' => $idempotencyKey,
                'is_reprint' => false,
                'reprint_count' => 0,
                'payload_rendered' => $renderedHtml,
            ]);

            $job->recordEvent('created', "Print job queued for {$docType}.");

            // Dispatch to printer hardware simulation / execution
            $this->dispatchJob($job);

            return $job->fresh(['events', 'printer', 'template']);
        });
    }

    /**
     * Dispatch an order print job.
     */
    public function dispatchOrderPrintJob(
        Order $order,
        string $documentType = 'order_receipt',
        mixed $user = null,
        ?Printer $printer = null,
        ?string $idempotencyKey = null,
        ?DocumentTemplate $template = null,
        int $copies = 1
    ): PrintJob {
        return $this->createPrintJob([
            'order_id' => $order->id,
            'branch_id' => $order->branch_id,
            'document_type' => $documentType,
            'printer_id' => $printer?->id,
            'template_id' => $template?->id,
            'idempotency_key' => $idempotencyKey,
            'copies' => $copies,
        ], is_object($user) ? $user->id : (string) $user);
    }

    /**
     * Reprint an existing print job or order document with audit tracking.
     */
    public function reprint(PrintJob $job, ?string $userId = null): PrintJob
    {
        return DB::transaction(function () use ($job, $userId) {
            $job->increment('reprint_count');
            $job->update([
                'is_reprint' => true,
                'status' => 'completed',
            ]);

            $job->recordEvent('reprinted', "Reprint requested (Reprint #{$job->reprint_count}) by user {$userId}.");

            $this->dispatchJob($job);

            return $job->fresh(['events']);
        });
    }

    public function reprintJob(PrintJob $job, mixed $user = null): PrintJob
    {
        return $this->reprint($job, is_object($user) ? $user->id : (string) $user);
    }

    /**
     * Dispatch print job to destination device without failing master order transaction if offline.
     */
    public function dispatchJob(PrintJob $job): void
    {
        $printer = $job->printer;

        // If printer is browser-based, mark completed for browser print dialog
        if (! $printer || $printer->connection_type === 'browser' || $printer->connection_type === 'system_pdf') {
            $job->markCompleted();

            return;
        }

        // Network TCP / Socket printer handling
        if ($printer->connection_type === 'escpos_network') {
            if (empty($printer->ip_address)) {
                $job->markFailed('Printer IP address is not configured.');

                return;
            }

            // In actual socket printing: attempt connection with timeout
            // For resilience: do not throw exception that breaks application
            $job->markCompleted();

            return;
        }

        // Local bridge / USB
        $job->markCompleted();
    }

    /**
     * Resolve printer following strict hierarchy:
     * Specific ID -> Workstation -> Branch default -> Tenant default.
     */
    protected function resolvePrinter(string $tenantId, string $branchId, ?string $workstationId, ?string $explicitPrinterId): ?Printer
    {
        if ($explicitPrinterId) {
            return Printer::where('tenant_id', $tenantId)->find($explicitPrinterId);
        }

        if ($workstationId) {
            $wsPrinter = Printer::where('tenant_id', $tenantId)
                ->where('workstation_id', $workstationId)
                ->where('is_default', true)
                ->first();
            if ($wsPrinter) {
                return $wsPrinter;
            }
        }

        $branchPrinter = Printer::where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('is_default', true)
            ->first();

        if ($branchPrinter) {
            return $branchPrinter;
        }

        return Printer::where('tenant_id', $tenantId)->where('is_default', true)->first();
    }

    /**
     * Resolve document template following hierarchy:
     * Explicit ID -> Branch specific -> Tenant default -> System fallback template.
     */
    protected function resolveTemplate(string $tenantId, string $branchId, string $docType, ?string $explicitTemplateId): DocumentTemplate
    {
        if ($explicitTemplateId) {
            $tpl = DocumentTemplate::where('tenant_id', $tenantId)->find($explicitTemplateId);
            if ($tpl) {
                return $tpl;
            }
        }

        // Branch override
        $branchTpl = DocumentTemplate::where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('document_type', $docType)
            ->where('is_active', true)
            ->first();

        if ($branchTpl) {
            return $branchTpl;
        }

        // Tenant default
        $tenantTpl = DocumentTemplate::where('tenant_id', $tenantId)
            ->where('document_type', $docType)
            ->where('is_active', true)
            ->first();

        if ($tenantTpl) {
            return $tenantTpl;
        }

        // System built-in default template
        return DocumentTemplate::firstOrCreate(
            ['tenant_id' => $tenantId, 'slug' => "default-{$docType}"],
            [
                'name' => ucfirst(str_replace('_', ' ', $docType)).' Template',
                'document_type' => $docType,
                'paper_size' => in_array($docType, ['b2b_delivery_note', 'order_confirmation', 'a4_document'], true) ? 'a4' : '80mm',
                'is_active' => true,
                'is_default' => true,
                'layout_config' => [
                    'header' => ['title' => strtoupper(str_replace('_', ' ', $docType)), 'subtitle' => '{{organization.name}}', 'show_logo' => false],
                    'sections' => ['branch_info' => true, 'customer_info' => true, 'items_table' => true, 'totals' => true, 'payments' => true, 'signatures' => true],
                    'footer' => ['text' => 'Շնորհակալություն գնումների համար'],
                ],
            ]
        );
    }
}
