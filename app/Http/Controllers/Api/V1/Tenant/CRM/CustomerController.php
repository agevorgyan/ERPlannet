<?php

namespace App\Http\Controllers\Api\V1\Tenant\CRM;

use App\Domain\CRM\Actions\AddCustomerAddressAction;
use App\Domain\CRM\Actions\CreateCustomerAction;
use App\Domain\CRM\Actions\MergeCustomersAction;
use App\Domain\CRM\Actions\UpdateCustomerAction;
use App\Domain\CRM\Models\Customer;
use App\Domain\CRM\Models\CustomerActivity;
use App\Domain\CRM\Models\CustomerNote;
use App\Domain\CRM\Models\CustomerSource;
use App\Domain\CRM\Services\CustomerAnalyticsService;
use App\Domain\CRM\Services\CustomerDuplicateDetectionService;
use App\Domain\CRM\Services\CustomerLoyaltyService;
use App\Http\Controllers\Controller;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected CreateCustomerAction $createAction,
        protected UpdateCustomerAction $updateAction,
        protected AddCustomerAddressAction $addAddressAction,
        protected CustomerLoyaltyService $loyaltyService,
        protected CustomerAnalyticsService $analyticsService,
        protected CustomerDuplicateDetectionService $duplicateService,
        protected MergeCustomersAction $mergeAction
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Customer::with([
            'defaultAddress',
            'lastUsedAddress',
            'individual',
            'company',
            'loyaltyAccount',
            'primaryBranch',
            'acquisitionSource',
        ]);

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('branch_id')) {
            $query->where('primary_branch_id', $request->query('branch_id'));
        }

        if ($request->filled('source_id')) {
            $query->where('acquisition_source_id', $request->query('source_id'));
        }

        if ($request->filled('loyalty_tier')) {
            $query->where('loyalty_tier', $request->query('loyalty_tier'));
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ilike', "%{$search}%")
                    ->orWhere('last_name', 'ilike', "%{$search}%")
                    ->orWhere('company_name', 'ilike', "%{$search}%")
                    ->orWhere('display_name', 'ilike', "%{$search}%")
                    ->orWhere('customer_code', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('tax_id', 'like', "%{$search}%");
            });
        }

        $sortField = $request->query('sort_by', 'created_at');
        $sortDirection = strtolower($request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['created_at', 'total_spent', 'orders_count', 'customer_score', 'first_name', 'company_name', 'last_ordered_at'];
        if (in_array($sortField, $allowedSorts, true)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest();
        }

        $perPage = $request->integer('per_page', 25);
        $customers = $query->paginate($perPage);

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
            'type' => ['nullable', 'string', 'in:individual,company'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'legal_address' => ['nullable', 'string', 'max:255'],
            'physical_address' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'director_name' => ['nullable', 'string', 'max:150'],
            'accountant_name' => ['nullable', 'string', 'max:150'],
            'purchasing_manager_name' => ['nullable', 'string', 'max:150'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0'],
            'is_postpaid_allowed' => ['nullable', 'boolean'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'primary_branch_id' => ['nullable', 'uuid'],
            'acquisition_source_id' => ['nullable', 'uuid'],
            'assigned_manager_id' => ['nullable', 'uuid'],
            'custom_discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'marketing_sms_consent' => ['nullable', 'boolean'],
            'marketing_email_consent' => ['nullable', 'boolean'],
            'marketing_calls_consent' => ['nullable', 'boolean'],
            'address' => ['nullable', 'array'],
            'address.title' => ['nullable', 'string', 'max:100'],
            'address.country' => ['nullable', 'string', 'max:10'],
            'address.province' => ['nullable', 'string', 'max:100'],
            'address.city' => ['nullable', 'string', 'max:100'],
            'address.postal_code' => ['nullable', 'string', 'max:20'],
            'address.street' => ['nullable', 'string', 'max:200'],
            'address.building' => ['nullable', 'string', 'max:50'],
            'address.entrance' => ['nullable', 'string', 'max:20'],
            'address.floor' => ['nullable', 'string', 'max:20'],
            'address.apartment' => ['nullable', 'string', 'max:20'],
            'address.door_code' => ['nullable', 'string', 'max:50'],
            'address.delivery_instructions' => ['nullable', 'string'],
            'address.address_line_1' => ['nullable', 'string', 'max:255'],
            'address.address_line_2' => ['nullable', 'string', 'max:255'],
            'contacts' => ['nullable', 'array'],
            'contacts.*.name' => ['required_with:contacts', 'string', 'max:150'],
            'contacts.*.position' => ['nullable', 'string', 'max:100'],
            'contacts.*.phone' => ['required_with:contacts', 'string', 'max:50'],
            'contacts.*.email' => ['nullable', 'string', 'email', 'max:255'],
            'contacts.*.is_primary' => ['nullable', 'boolean'],
        ]);

        $userId = auth()->id();
        $customer = $this->createAction->execute($validated, $userId);

        return response()->json([
            'success' => true,
            'message' => 'Հաճախորդը հաջողությամբ ստեղծվել է:',
            'data' => $customer->load(['individual', 'company', 'contacts', 'addresses', 'loyaltyAccount']),
        ], 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        $customer->load([
            'individual',
            'company',
            'contacts',
            'addresses',
            'loyaltyAccount.transactions' => fn ($q) => $q->limit(20),
            'activities' => fn ($q) => $q->with('user')->limit(30),
            'notes' => fn ($q) => $q->with('author')->limit(30),
            'orders' => fn ($q) => $q->with('items')->latest()->limit(15),
            'primaryBranch',
            'acquisitionSource',
            'assignedManager',
        ]);

        // Calculate live analytics
        $topProducts = $this->analyticsService->getTopProducts($customer, 5);

        return response()->json([
            'success' => true,
            'data' => array_merge($customer->toArray(), [
                'analytics' => [
                    'top_products' => $topProducts,
                    'effective_discount_percent' => $customer->getEffectiveDiscountPercent(),
                ],
            ]),
        ]);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', 'in:individual,company'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'legal_address' => ['nullable', 'string', 'max:255'],
            'physical_address' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'director_name' => ['nullable', 'string', 'max:150'],
            'accountant_name' => ['nullable', 'string', 'max:150'],
            'purchasing_manager_name' => ['nullable', 'string', 'max:150'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0'],
            'is_postpaid_allowed' => ['nullable', 'boolean'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'status' => ['nullable', 'string', 'in:active,inactive,blocked,archived'],
            'primary_branch_id' => ['nullable', 'uuid'],
            'assigned_manager_id' => ['nullable', 'uuid'],
            'acquisition_source_id' => ['nullable', 'uuid'],
            'custom_discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'marketing_sms_consent' => ['nullable', 'boolean'],
            'marketing_email_consent' => ['nullable', 'boolean'],
            'marketing_calls_consent' => ['nullable', 'boolean'],
        ]);

        $userId = auth()->id();
        $updated = $this->updateAction->execute($customer, $validated, $userId);

        return response()->json([
            'success' => true,
            'message' => 'Հաճախորդի տվյալները թարմացվել են:',
            'data' => $updated,
        ]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $userId = auth()->id();

        CustomerActivity::create([
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customer->id,
            'type' => CustomerActivity::TYPE_STATUS_CHANGED,
            'title' => 'Հաճախորդի արխիվացում',
            'content' => "Հաճախորդ {$customer->customer_code} արխիվացվել է",
            'user_id' => $userId,
        ]);

        $customer->update(['status' => Customer::STATUS_ARCHIVED]);
        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Հաճախորդը հաջողությամբ արխիվացվել է:',
        ]);
    }

    public function restore(string $id): JsonResponse
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->restore();
        $customer->update(['status' => Customer::STATUS_ACTIVE]);

        CustomerActivity::create([
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customer->id,
            'type' => CustomerActivity::TYPE_STATUS_CHANGED,
            'title' => 'Հաճախորդի վերականգնում',
            'content' => "Հաճախորդ {$customer->customer_code} վերականգնվել է արխիվից",
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Հաճախորդը վերականգնվել է:',
            'data' => $customer,
        ]);
    }

    public function addAddress(Request $request, string $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:10'],
            'province' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'street' => ['nullable', 'string', 'max:200'],
            'building' => ['nullable', 'string', 'max:50'],
            'entrance' => ['nullable', 'string', 'max:20'],
            'floor' => ['nullable', 'string', 'max:20'],
            'apartment' => ['nullable', 'string', 'max:20'],
            'door_code' => ['nullable', 'string', 'max:50'],
            'delivery_instructions' => ['nullable', 'string'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
            'is_last_used' => ['nullable', 'boolean'],
        ]);

        $userId = auth()->id();
        $address = $this->addAddressAction->execute($customer, $validated, $userId);

        return response()->json([
            'success' => true,
            'message' => 'Հասցեն ավելացվել է:',
            'data' => $address,
        ], 201);
    }

    public function checkDuplicate(Request $request): JsonResponse
    {
        $tenantId = $this->tenantContext->getTenantId() ?? auth()->user()?->tenant_id;
        if (! $tenantId) {
            return response()->json(['success' => false, 'message' => 'Tenant missing'], 400);
        }

        $phone = $request->input('phone');
        $email = $request->input('email');
        $taxId = $request->input('tax_id');
        $excludeId = $request->input('exclude_id');

        $duplicates = $this->duplicateService->findDuplicates($tenantId, $phone, $email, $taxId, $excludeId);

        return response()->json([
            'success' => true,
            'has_duplicate' => ! empty($duplicates),
            'duplicates' => $duplicates,
        ]);
    }

    public function sources(): JsonResponse
    {
        $tenantId = $this->tenantContext->getTenantId() ?? auth()->user()?->tenant_id;
        $sources = CustomerSource::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sources,
        ]);
    }

    public function timeline(string $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $activities = CustomerActivity::withoutGlobalScopes()
            ->where('tenant_id', $customer->tenant_id)
            ->where('customer_id', $customer->id)
            ->with('user')
            ->latest()
            ->paginate(30);

        return response()->json([
            'success' => true,
            'data' => $activities->items(),
            'meta' => [
                'pagination' => [
                    'current_page' => $activities->currentPage(),
                    'total' => $activities->total(),
                    'last_page' => $activities->lastPage(),
                ],
            ],
        ]);
    }

    public function addNote(Request $request, string $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'category' => ['nullable', 'string', 'in:general,preference,complaint,financial,call,task'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $note = CustomerNote::create([
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customer->id,
            'author_user_id' => auth()->id() ?? $customer->created_by_user_id,
            'category' => $validated['category'] ?? CustomerNote::CATEGORY_GENERAL,
            'content' => $validated['content'],
            'is_pinned' => (bool) ($validated['is_pinned'] ?? false),
        ]);

        CustomerActivity::create([
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customer->id,
            'type' => CustomerActivity::TYPE_NOTE,
            'title' => 'Նոր նշում CRM-ում',
            'content' => mb_substr($note->content, 0, 100),
            'reference_type' => CustomerNote::class,
            'reference_id' => $note->id,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Գրառումը պահպանվել է:',
            'data' => $note->load('author'),
        ], 201);
    }

    public function adjustLoyalty(Request $request, string $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'points_delta' => ['required', 'numeric'],
            'reason' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:manual_adj,birthday_gift,earn,redeem'],
        ]);

        $tx = $this->loyaltyService->recordTransaction(
            $customer,
            $validated['type'] ?? 'manual_adj',
            (float) $validated['points_delta'],
            $validated['reason'],
            null,
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Լոյալության միավորները ճշգրտվել են:',
            'data' => $tx,
            'account' => $customer->fresh('loyaltyAccount')->loyaltyAccount,
        ]);
    }

    public function merge(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'target_customer_id' => ['required', 'uuid'],
            'source_customer_id' => ['required', 'uuid'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $target = Customer::findOrFail($validated['target_customer_id']);
        $source = Customer::findOrFail($validated['source_customer_id']);

        $merged = $this->mergeAction->execute($target, $source, auth()->id(), $validated['notes'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Հաճախորդները հաջողությամբ միավորվել են:',
            'data' => $merged,
        ]);
    }
}
