<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import {
  UserPlus,
  Search,
  Filter,
  MoreVertical,
  Trash2,
  Edit2,
  Mail,
  Shield,
  CheckCircle,
  XCircle,
} from 'lucide-vue-next'

import AppCard from '@/components/ui/AppCard.vue'
import AppTable from '@/components/ui/AppTable.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import { usersApi, type UserItem } from '@/api/users'
import { useAuthStore } from '@/stores/authStore'
import { useUiStore } from '@/stores/uiStore'

const { t } = useI18n()
const authStore = useAuthStore()
const uiStore = useUiStore()

const isLoading = ref(false)
const usersList = ref<UserItem[]>([])
const searchQuery = ref('')
const selectedRole = ref('')
const currentPage = ref(1)
const perPage = ref(10)
const totalItems = ref(0)
const totalPages = ref(1)

// Invite / Edit User modal
const isModalOpen = ref(false)
const isModalSaving = ref(false)
const editingUser = ref<UserItem | null>(null)

const userForm = ref({
  name: '',
  email: '',
  role: 'member',
  status: 'active' as 'active' | 'inactive',
})

const roleOptions = [
  { label: 'Admin (Full Access)', value: 'admin' },
  { label: 'Manager (Operations)', value: 'manager' },
  { label: 'Accountant (Financials)', value: 'accountant' },
  { label: 'Member / Operator', value: 'member' },
]

const columns = [
  { key: 'user', label: 'User' },
  { key: 'role', label: 'Role' },
  { key: 'status', label: 'Status' },
  { key: 'created_at', label: 'Created' },
  { key: 'actions', label: 'Actions', align: 'right' as const },
]

async function fetchUsers() {
  isLoading.value = true
  try {
    const res = await usersApi.getUsers({
      page: currentPage.value,
      per_page: perPage.value,
      search: searchQuery.value,
      role: selectedRole.value,
    })
    usersList.value = res.data
    totalItems.value = res.meta.total
    totalPages.value = res.meta.last_page
  } catch (err: any) {
    uiStore.error('Failed to load users list')
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchUsers()
})

function openInviteModal() {
  editingUser.value = null
  userForm.value = {
    name: '',
    email: '',
    role: 'member',
    status: 'active',
  }
  isModalOpen.value = true
}

function openEditModal(user: UserItem) {
  editingUser.value = user
  userForm.value = {
    name: user.name,
    email: user.email,
    role: user.role || 'member',
    status: user.status || 'active',
  }
  isModalOpen.value = true
}

async function saveUser() {
  if (!userForm.value.name || !userForm.value.email) {
    uiStore.error('Please fill in user name and email')
    return
  }

  isModalSaving.value = true
  try {
    if (editingUser.value) {
      await usersApi.updateUser(editingUser.value.id, userForm.value)
      uiStore.success('User updated successfully')
    } else {
      await usersApi.inviteUser(userForm.value)
      uiStore.success('User invited successfully')
    }
    isModalOpen.value = false
    fetchUsers()
  } catch (err: any) {
    uiStore.error(err.message || 'Operation failed')
  } finally {
    isModalSaving.value = false
  }
}

function confirmDeleteUser(user: UserItem) {
  uiStore.confirm({
    title: 'Delete Team Member',
    message: `Are you sure you want to remove ${user.name} (${user.email}) from this workspace? This will revoke all their active sessions.`,
    confirmText: 'Delete User',
    cancelText: 'Cancel',
    type: 'danger',
    onConfirm: async () => {
      await usersApi.deleteUser(user.id)
      uiStore.success(`User ${user.name} removed`)
      fetchUsers()
    },
  })
}
</script>

<template>
  <div class="space-y-6 text-left">
    <!-- Header with Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">
          {{ t('users.title') }}
        </h1>
        <p class="text-xs text-slate-500 mt-0.5">
          Manage workspace team members, departments, and individual access permissions.
        </p>
      </div>

      <AppButton
        v-if="authStore.hasPermission('users.create')"
        variant="primary"
        size="md"
        @click="openInviteModal"
      >
        <template #leading>
          <UserPlus class="w-4 h-4" />
        </template>
        <span>{{ t('users.inviteUser') }}</span>
      </AppButton>
    </div>

    <!-- Search & Filter Controls -->
    <AppCard no-padding>
      <div class="p-4 flex flex-col sm:flex-row items-center justify-between gap-3 border-b border-slate-100">
        <div class="relative w-full sm:w-72">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by name or email..."
            class="w-full text-xs text-slate-800 bg-slate-50 rounded-xl border border-slate-200 pl-9 pr-3 py-2 outline-none focus:bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10"
            @input="fetchUsers"
          />
          <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" />
        </div>

        <div class="w-full sm:w-48">
          <select
            v-model="selectedRole"
            class="w-full text-xs text-slate-700 bg-slate-50 rounded-xl border border-slate-200 px-3 py-2 outline-none focus:bg-white focus:border-brand-500"
            @change="fetchUsers"
          >
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="manager">Manager</option>
            <option value="accountant">Accountant</option>
            <option value="member">Member</option>
          </select>
        </div>
      </div>

      <!-- Users Table -->
      <AppTable
        :columns="columns"
        :items="usersList"
        :loading="isLoading"
        empty-title="No team members found"
        empty-description="Invite teammates to collaborate on this workspace."
      >
        <!-- User column slot -->
        <template #cell-user="{ item }">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-brand-100 text-brand-700 font-bold flex items-center justify-center text-xs shrink-0 border border-brand-200">
              {{ item.name.split(' ').map((n: string) => n[0]).join('').substring(0, 2).toUpperCase() }}
            </div>
            <div>
              <div class="text-xs font-bold text-slate-900">{{ item.name }}</div>
              <div class="text-[11px] text-slate-400">{{ item.email }}</div>
            </div>
          </div>
        </template>

        <!-- Role column slot -->
        <template #cell-role="{ item }">
          <AppBadge
            :variant="item.role === 'admin' ? 'brand' : item.role === 'manager' ? 'purple' : 'neutral'"
            size="sm"
          >
            {{ item.role_name || item.role }}
          </AppBadge>
        </template>

        <!-- Status column slot -->
        <template #cell-status="{ item }">
          <AppBadge
            :variant="item.status === 'active' ? 'success' : 'neutral'"
            size="sm"
            dot
          >
            {{ item.status === 'active' ? 'Active' : 'Inactive' }}
          </AppBadge>
        </template>

        <!-- Created column slot -->
        <template #cell-created_at="{ item }">
          <span class="text-xs text-slate-500">
            {{ item.created_at ? new Date(item.created_at).toLocaleDateString() : '—' }}
          </span>
        </template>

        <!-- Actions slot -->
        <template #cell-actions="{ item }">
          <div class="flex items-center justify-end gap-1.5">
            <button
              v-if="authStore.hasPermission('users.edit')"
              type="button"
              class="p-1.5 text-slate-400 hover:text-brand-600 rounded-lg hover:bg-slate-100 transition-colors"
              title="Edit User"
              @click="openEditModal(item)"
            >
              <Edit2 class="w-4 h-4" />
            </button>
            <button
              v-if="authStore.hasPermission('users.delete') && item.id !== authStore.currentUser?.id"
              type="button"
              class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 transition-colors"
              title="Delete User"
              @click="confirmDeleteUser(item)"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </template>
      </AppTable>

      <AppPagination
        v-if="totalPages > 1"
        :current-page="currentPage"
        :total-pages="totalPages"
        :total-items="totalItems"
        :per-page="perPage"
        @update:current-page="(p) => { currentPage = p; fetchUsers(); }"
      />
    </AppCard>

    <!-- Invite / Edit User Modal -->
    <AppModal
      v-model="isModalOpen"
      :title="editingUser ? 'Edit Team Member' : 'Invite New Teammate'"
      :subtitle="editingUser ? 'Update member role and account status' : 'Send an invitation link with assigned role'"
      size="md"
    >
      <form class="space-y-4 text-left" @submit.prevent="saveUser">
        <AppInput
          v-model="userForm.name"
          label="Full Name"
          placeholder="e.g. Karen Melkonyan"
          required
        />

        <AppInput
          v-model="userForm.email"
          type="email"
          label="Work Email Address"
          placeholder="karen@company.am"
          :disabled="!!editingUser"
          required
        />

        <AppSelect
          v-model="userForm.role"
          :options="roleOptions"
          label="Assigned Role"
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
            :loading="isModalSaving"
          >
            {{ editingUser ? 'Update Member' : 'Send Invite' }}
          </AppButton>
        </div>
      </form>
    </AppModal>
  </div>
</template>
