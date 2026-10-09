<?php

namespace App\Http\Controllers\Api\V1\Tenant\CRM;

use App\Domain\CRM\Models\Customer;
use App\Domain\CRM\Models\CustomerAddress;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::with('defaultAddress');

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate($request->integer('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $customers->items(),
            'meta' => [
                'pagination' => [
                    'current_page' => $customers->currentPage(),
                    'per_page' => $customers->perPage(),
                    'total' => $customers->total(),
                    'last_page' => $customers->lastPage(),
                ],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'address' => ['nullable', 'array'],
            'address.title' => ['nullable', 'string', 'max:100'],
            'address.city' => ['nullable', 'string', 'max:100'],
            'address.address_line_1' => ['required_with:address', 'string', 'max:255'],
            'address.address_line_2' => ['nullable', 'string', 'max:255'],
            'address.floor' => ['nullable', 'string', 'max:20'],
            'address.apartment' => ['nullable', 'string', 'max:20'],
        ]);

        $customer = DB::transaction(function () use ($validated) {
            $customer = Customer::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'] ?? null,
                'company_name' => $validated['company_name'] ?? null,
                'tax_id' => $validated['tax_id'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'],
                'source' => $validated['source'] ?? 'direct',
                'notes' => $validated['notes'] ?? null,
            ]);

            if (! empty($validated['address'])) {
                CustomerAddress::create([
                    'tenant_id' => $customer->tenant_id,
                    'customer_id' => $customer->id,
                    'title' => $validated['address']['title'] ?? 'Default',
                    'city' => $validated['address']['city'] ?? 'Yerevan',
                    'address_line_1' => $validated['address']['address_line_1'],
                    'address_line_2' => $validated['address']['address_line_2'] ?? null,
                    'floor' => $validated['address']['floor'] ?? null,
                    'apartment' => $validated['address']['apartment'] ?? null,
                    'is_default' => true,
                ]);
            }

            return $customer;
        });

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully.',
            'data' => $customer->load('addresses'),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $customer = Customer::with(['addresses', 'orders' => fn ($q) => $q->latest()->limit(10)])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $customer,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully.',
            'data' => $customer->load('addresses'),
        ]);
    }

    public function addAddress(Request $request, string $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:20'],
            'apartment' => ['nullable', 'string', 'max:20'],
            'entry_code' => ['nullable', 'string', 'max:50'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['is_default'])) {
            $customer->addresses()->update(['is_default' => false]);
        }

        $address = CustomerAddress::create([
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customer->id,
            'title' => $validated['title'] ?? 'Other',
            'city' => $validated['city'] ?? 'Yerevan',
            'address_line_1' => $validated['address_line_1'],
            'address_line_2' => $validated['address_line_2'] ?? null,
            'floor' => $validated['floor'] ?? null,
            'apartment' => $validated['apartment'] ?? null,
            'entry_code' => $validated['entry_code'] ?? null,
            'is_default' => $validated['is_default'] ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Address added successfully.',
            'data' => $address,
        ], 201);
    }
}
