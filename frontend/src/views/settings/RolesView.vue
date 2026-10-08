<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { ShieldCheck, Plus, Check, Edit2, Trash2, Shield, Lock } from 'lucide-vue-next'

import AppCard from '@/components/ui/AppCard.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppCheckbox from '@/components/ui/AppCheckbox.vue'
import { rolesApi, type RoleItem } from '@/api/roles'
import { useAuthStore } from '@/stores/authStore'
import { useUiStore } from '@/stores/uiStore'

const { t } = useI18n()
const authStore = useAuthStore()
const uiStore = useUiStore()

const isLoading = ref(false)
const rolesList = ref<RoleItem[]>([])
const selectedRoleId = ref<string>('')
const selectedRole = ref<RoleItem | null>(null)

// Modal state
const isModalOpen = ref(false)
const isSaving = ref(false)
const newRoleForm = ref({
  name: '',
  description: '',
})

// Permissions dictionary grouped by module
const modulePermissions = [
  {
    module: 'Orders & Sales',
    permissions: [
      { key: 'orders.view', label: 'View Orders & POS transactions' },
      { key: 'orders.create', label: 'Create new orders & checkout' },
      { key: 'orders.edit', label: 'Edit orders & apply discounts' },
      { key: 'orders.delete', label: 'Void / Refund orders & cancel' },
    ],
  },
  {
    module: 'User Management',
    permissions: [
      { key: 'users.view', label: 'View team members' },
      { key: 'users.create', label: 'Invite new teammates' },
      { key: 'users.edit', label: 'Modify user profiles' },
      { key: 'users.delete', label: 'Remove users from workspace' },
    ],
  },
  {
    module: 'Roles & Security',
    permissions: [
      { key: 'roles.view', label: 'View access roles & policies' },
      { key: 'roles.manage', label: 'Configure and assign role permissions' },
    ],
  },
  {
    module: 'Tenant Settings & Audit',
    permissions: [
      { key: 'settings.view', label: 'View organization details' },
      { key: 'settings.edit', label: 'Update organization profile & domains' },
      { key: 'audit.view', label: 'View compliance audit logs' },
    ],
  },
  {
    module: 'Billing & Subscriptions',
    permissions: [
      { key: 'billing.view', label: 'View subscription plan & invoices' },
      { key: 'billing.manage', label: 'Upgrade plan, change card, cancel' },
    ],
  },
]

async function fetchRoles() {
  isLoading.value = true
  try {
    const roles = await rolesApi.getRoles()
    rolesList.value = roles
    if (roles.length > 0 && !selectedRoleId.value) {
      selectRole(roles[0])
    }
  } finally {
    isLoading.value = false
  }
}

function selectRole(role: RoleItem) {
  selectedRoleId.value = role.id
  selectedRole.value = JSON.parse(JSON.stringify(role))
}

function togglePermission(key: string) {
  if (!selectedRole.value || selectedRole.value.is_system) return
  const perms = selectedRole.value.permissions
  if (perms.includes(key)) {
    selectedRole.value.permissions = perms.filter((p: string) => p !== key)
  } else {
    selectedRole.value.permissions.push(key)
  }
}

async function saveRolePermissions() {
  if (!selectedRole.value) return
  isSaving.value = true
  try {
    await rolesApi.updateRole(selectedRole.value.id, {
      name: selectedRole.value.name,
      description: selectedRole.value.description,
      permissions: selectedRole.value.permissions,
    })
    uiStore.success(`Permissions updated for role ${selectedRole.value.name}`)
    fetchRoles()
  } catch (err: any) {
    uiStore.error('Failed to update permissions')
  } finally {
    isSaving.value = false
  }
}

function openCreateModal() {
  newRoleForm.value = { name: '', description: '' }
  isModalOpen.value = true
}

async function handleCreateRole() {
  if (!newRoleForm.value.name) return
  isSaving.value = true
  try {
    const created = await rolesApi.createRole({
      name: newRoleForm.value.name,
      description: newRoleForm.value.description,
      permissions: ['orders.view', 'settings.view'],
    })
    uiStore.success(`Role "${created.name}" created`)
    isModalOpen.value = false
    await fetchRoles()
    selectRole(created)
  } catch (err: any) {
    uiStore.error('Could not create role')
  } finally {
    isSaving.value = false
  }
}

function confirmDeleteRole(role: RoleItem) {
  uiStore.confirm({
    title: 'Delete Role',
    message: `Are you sure you want to delete role "${role.name}"? Users assigned to this role will need to be reassigned.`,
    confirmText: 'Delete Role',
    cancelText: 'Cancel',
    type: 'danger',
    onConfirm: async () => {
      await rolesApi.deleteRole(role.id)
      uiStore.success(`Role deleted`)
      fetchRoles()
    },
  })
}

onMounted(() => {
  fetchRoles()
})
</script>

<template>
  <div class="space-y-6 text-left">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">
          {{ t('roles.title') }}
        </h1>
        <p class="text-xs text-slate-500 mt-0.5">
          Role-Based Access Control (RBAC): Configure granular permission matrix by operational module.
        </p>
      </div>

      <AppButton
        v-if="authStore.hasPermission('roles.manage')"
        variant="primary"
        size="md"
        @click="openCreateModal"
      >
        <template #leading>
          <Plus class="w-4 h-4" />
        </template>
        <span>{{ t('roles.createRole') }}</span>
      </AppButton>
    </div>

    <!-- Master-Detail Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left: Role List Selection -->
      <div class="lg:col-span-1 space-y-3">
        <div
          v-for="role in rolesList"
          :key="role.id"
          :class="[
            'p-4 rounded-2xl border transition-all cursor-pointer flex flex-col justify-between text-left',
            selectedRoleId === role.id
              ? 'bg-brand-50/70 border-brand-300 shadow-xs'
              : 'bg-white border-slate-100 hover:border-slate-200 shadow-2xs',
          ]"
          @click="selectRole(role)"
        >
          <div class="flex items-start justify-between">
            <div class="flex items-center gap-2.5">
              <div :class="['w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs', selectedRoleId === role.id ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-700']">
                <Shield class="w-4 h-4" />
              </div>
              <div>
                <h4 class="text-sm font-bold text-slate-900">{{ role.name }}</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ role.description }}</p>
              </div>
            </div>

            <AppBadge v-if="role.is_system" variant="neutral" size="sm">
              System
            </AppBadge>
          </div>

          <div class="mt-3 pt-3 border-t border-slate-100/80 flex items-center justify-between text-xs text-slate-500">
            <span>{{ role.permissions.length }} permissions</span>
            <button
              v-if="!role.is_system && authStore.hasPermission('roles.manage')"
              type="button"
              class="text-slate-400 hover:text-rose-600 p-1"
              title="Delete Role"
              @click.stop="confirmDeleteRole(role)"
            >
              <Trash2 class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      </div>

      <!-- Right: Permissions Matrix for Selected Role -->
      <div class="lg:col-span-2">
        <AppCard v-if="selectedRole" :title="`Permissions: ${selectedRole.name}`" :subtitle="selectedRole.description">
          <template #actions>
            <AppButton
              v-if="!selectedRole.is_system && authStore.hasPermission('roles.manage')"
              variant="primary"
              size="sm"
              :loading="isSaving"
              @click="saveRolePermissions"
            >
              Save Permissions
            </AppButton>
          </template>

          <div v-if="selectedRole.is_system" class="mb-4 p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-center gap-2">
            <Lock class="w-4 h-4 shrink-0 text-amber-600" />
            <span>This is a built-in system role. Core permissions are protected to maintain platform integrity.</span>
          </div>

          <!-- Modules Checklist -->
          <div class="space-y-6">
            <div
              v-for="mod in modulePermissions"
              :key="mod.module"
              class="pb-5 border-b border-slate-100 last:border-0"
            >
              <h5 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">
                {{ mod.module }}
              </h5>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div
                  v-for="p in mod.permissions"
                  :key="p.key"
                  :class="[
                    'p-3 rounded-xl border flex items-center justify-between transition-colors',
                    selectedRole.permissions.includes(p.key)
                      ? 'bg-brand-50/40 border-brand-200'
                      : 'bg-white border-slate-200/80 hover:bg-slate-50',
                    selectedRole.is_system ? 'cursor-not-allowed opacity-80' : 'cursor-pointer',
                  ]"
                  @click="togglePermission(p.key)"
                >
                  <div class="pr-2">
                    <span class="text-xs font-semibold text-slate-800 block">{{ p.label }}</span>
                    <span class="text-[10px] text-slate-400 font-mono">{{ p.key }}</span>
                  </div>

                  <div
                    :class="[
                      'w-5 h-5 rounded-md flex items-center justify-center shrink-0 border transition-all',
                      selectedRole.permissions.includes(p.key)
                        ? 'bg-brand-600 border-brand-600 text-white'
                        : 'border-slate-300 bg-white',
                    ]"
                  >
                    <Check v-if="selectedRole.permissions.includes(p.key)" class="w-3.5 h-3.5 stroke-[3]" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </AppCard>
      </div>
    </div>

    <!-- Create Role Modal -->
    <AppModal
      v-model="isModalOpen"
      title="Create Custom Access Role"
      subtitle="Define a new role and assign operational permissions"
      size="md"
    >
      <form class="space-y-4 text-left" @submit.prevent="handleCreateRole">
        <AppInput
          v-model="newRoleForm.name"
          label="Role Name"
          placeholder="e.g. Inventory Dispatcher"
          required
        />

        <AppInput
          v-model="newRoleForm.description"
          label="Description"
          placeholder="Responsibilities and access scope..."
        />

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
          <AppButton
            variant="outline"
            size="md"
            @click="isModalOpen = false"
          >
            Cancel
          </AppButton>
          <AppButton
            type="submit"
            variant="primary"
            size="md"
            :loading="isSaving"
          >
            Create Role
          </AppButton>
        </div>
      </form>
    </AppModal>
  </div>
</template>
