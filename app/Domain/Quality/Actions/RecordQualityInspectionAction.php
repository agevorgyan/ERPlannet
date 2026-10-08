<?php

namespace App\Domain\Quality\Actions;

use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Domain\Manufacturing\Models\ProductionOrder;
use App\Domain\Quality\Models\QualityInspection;
use App\Domain\Quality\Models\QualityInspectionItem;
use App\Domain\Quality\Services\QualityInspectionNumberGenerator;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class RecordQualityInspectionAction
{
    public function __construct(
        protected EntitlementManagerInterface $entitlements,
        protected QualityInspectionNumberGenerator $numberGenerator,
        protected TenantContext $tenantContext
    ) {}

    public function execute(
        string $productionOrderId,
        string $inspectorId,
        array $items,
        string $standardApplied = 'ISO 22000:2018',
        ?string $notes = null
    ): QualityInspection {
        $tenant = $this->tenantContext->getTenant();
        if (!$tenant) {
            throw new \RuntimeException('Tenant context not set.');
        }

        // 1. Entitlement check
        $this->entitlements->assertCan('feature.iso22000');

        if (empty($items)) {
            throw new \InvalidArgumentException('Inspection must contain at least one checkpoint parameter.');
        }

        $order = ProductionOrder::findOrFail($productionOrderId);

        return DB::transaction(function () use (
            $tenant,
            $order,
            $inspectorId,
            $items,
            $standardApplied,
            $notes
        ) {
            $inspectionNumber = $this->numberGenerator->generate($tenant);

            $hasFailed = false;
            $passedCount = 0;
            $totalCount = count($items);

            $evaluatedItems = [];

            foreach ($items as $itemData) {
                $actual = (string) $itemData['actual_value'];
                $isPassed = $itemData['is_passed'] ?? true;

                // Validate numeric tolerances if min/max set
                if (is_numeric($actual)) {
                    $numericVal = (float) $actual;
                    if (isset($itemData['min_value']) && $numericVal < (float) $itemData['min_value']) {
                        $isPassed = false;
                    }
                    if (isset($itemData['max_value']) && $numericVal > (float) $itemData['max_value']) {
                        $isPassed = false;
                    }
                }

                if (!$isPassed) {
                    $hasFailed = true;
                } else {
                    $passedCount++;
                }

                $evaluatedItems[] = array_merge($itemData, ['is_passed' => $isPassed]);
            }

            $overallStatus = $hasFailed ? 'failed' : 'passed';
            $overallScore = $totalCount > 0 ? round(($passedCount / $totalCount) * 100, 2) : 100.0;

            $inspection = QualityInspection::create([
                'tenant_id' => $tenant->id,
                'production_order_id' => $order->id,
                'inspector_id' => $inspectorId,
                'inspection_number' => $inspectionNumber,
                'status' => $overallStatus,
                'standard_applied' => $standardApplied,
                'overall_score' => $overallScore,
                'notes' => $notes,
                'inspected_at' => now(),
                'created_at' => now(),
            ]);

            foreach ($evaluatedItems as $item) {
                QualityInspectionItem::create([
                    'tenant_id' => $tenant->id,
                    'quality_inspection_id' => $inspection->id,
                    'parameter_name' => $item['parameter_name'],
                    'critical_control_point' => $item['critical_control_point'] ?? null,
                    'target_value' => $item['target_value'] ?? null,
                    'min_value' => $item['min_value'] ?? null,
                    'max_value' => $item['max_value'] ?? null,
                    'actual_value' => (string) $item['actual_value'],
                    'unit' => $item['unit'] ?? null,
                    'is_passed' => $item['is_passed'],
                    'deviation_notes' => $item['deviation_notes'] ?? null,
                    'created_at' => now(),
                ]);
            }

            // Update production order status based on QA result
            if ($overallStatus === 'passed') {
                $order->status = 'quality_check';
            } else {
                $order->status = 'rejected';
            }
            $order->save();

            return $inspection->load('items');
        });
    }
}
