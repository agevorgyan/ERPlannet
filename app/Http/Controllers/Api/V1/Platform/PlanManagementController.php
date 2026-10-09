<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Domain\Billing\Models\Feature;
use App\Domain\Billing\Models\Plan;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanManagementController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = Plan::with('features')->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:100', 'unique:plans,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_yearly' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer'],
            'features' => ['nullable', 'array'],
            'features.*.code' => ['required_with:features', 'string', 'exists:features,code'],
            'features.*.value' => ['required_with:features', 'string'],
        ]);

        $plan = DB::transaction(function () use ($validated) {
            $plan = Plan::create([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price_monthly' => $validated['price_monthly'],
                'price_yearly' => $validated['price_yearly'],
                'currency' => $validated['currency'] ?? 'AMD',
                'trial_days' => $validated['trial_days'] ?? 14,
                'sort_order' => $validated['sort_order'] ?? 0,
            ]);

            if (! empty($validated['features'])) {
                foreach ($validated['features'] as $featData) {
                    $feature = Feature::where('code', $featData['code'])->first();
                    if ($feature) {
                        $plan->features()->attach($feature->id, ['value' => $featData['value']]);
                    }
                }
            }

            return $plan;
        });

        return response()->json([
            'success' => true,
            'message' => 'Plan created successfully.',
            'data' => $plan->load('features'),
        ], 201);
    }
}
