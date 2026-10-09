<?php

namespace App\Domain\CRM\Policies;

use App\Domain\CRM\Models\Customer;
use App\Domain\IAM\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('crm.customers.view') || $user->hasPermissionTo('View Customers');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->tenant_id === $customer->tenant_id &&
            ($user->hasPermissionTo('crm.customers.view') || $user->hasPermissionTo('View Customers'));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('crm.customers.create') || $user->hasPermissionTo('Create Customers');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->tenant_id === $customer->tenant_id &&
            ($user->hasPermissionTo('crm.customers.edit') || $user->hasPermissionTo('Edit Customers'));
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->tenant_id === $customer->tenant_id &&
            ($user->hasPermissionTo('crm.customers.delete') || $user->hasPermissionTo('Delete Customers'));
    }

    public function restore(User $user, Customer $customer): bool
    {
        return $user->tenant_id === $customer->tenant_id &&
            ($user->hasPermissionTo('crm.customers.edit') || $user->hasPermissionTo('Edit Customers'));
    }

    public function merge(User $user): bool
    {
        return $user->hasPermissionTo('crm.customers.edit') || $user->hasPermissionTo('Edit Customers');
    }
}
