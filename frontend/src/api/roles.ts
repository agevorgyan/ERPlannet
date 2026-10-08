import { apiClient } from './client'

export interface PermissionDTO {
  id: string
  code: string
  name: string
  module: string
}

export interface RoleItem {
  id: string
  name: string
  slug: string
  description?: string
  is_system?: boolean
  permissions: string[]
  users_count?: number
}

export interface RoleDTO {
  id: string
  name: string
  slug: string
  description?: string
  permissions?: PermissionDTO[]
  users_count?: number
}

const defaultRoles: RoleItem[] = [
  {
    id: 'role_admin',
    name: 'Administrator',
    slug: 'admin',
    description: 'Unrestricted control over workspace, finances, users, and settings',
    is_system: true,
    permissions: [
      'orders.view', 'orders.create', 'orders.edit', 'orders.delete',
      'users.view', 'users.create', 'users.edit', 'users.delete',
      'roles.view', 'roles.manage',
      'settings.view', 'settings.edit', 'audit.view',
      'billing.view', 'billing.manage',
    ],
    users_count: 2,
  },
  {
    id: 'role_manager',
    name: 'Operations Manager',
    slug: 'manager',
    description: 'Manages sales orders, staff dispatch, and basic operational reporting',
    is_system: false,
    permissions: [
      'orders.view', 'orders.create', 'orders.edit',
      'users.view', 'users.create',
      'settings.view',
      'billing.view',
    ],
    users_count: 5,
  },
  {
    id: 'role_accountant',
    name: 'Chief Accountant',
    slug: 'accountant',
    description: 'Financial ledgers, fiscal invoices, and subscription overview',
    is_system: false,
    permissions: [
      'orders.view',
      'audit.view',
      'billing.view', 'billing.manage',
    ],
    users_count: 1,
  },
  {
    id: 'role_cashier',
    name: 'Cashier / POS Operator',
    slug: 'cashier',
    description: 'Point-of-sale terminal checkout and daily shift management',
    is_system: false,
    permissions: [
      'orders.view', 'orders.create',
    ],
    users_count: 8,
  },
]

export const rolesApi = {
  list: async (): Promise<RoleItem[]> => {
    try {
      const res = await apiClient.get('/roles')
      return res.data.data
    } catch {
      return defaultRoles
    }
  },

  getRoles: async (): Promise<RoleItem[]> => {
    return rolesApi.list()
  },

  create: async (payload: { name: string; description?: string; slug?: string; permissions?: string[] }): Promise<RoleItem> => {
    try {
      const res = await apiClient.post('/roles', payload)
      return res.data.data
    } catch {
      const newRole: RoleItem = {
        id: `role_${Date.now()}`,
        name: payload.name,
        slug: payload.slug || payload.name.toLowerCase().replace(/\s+/g, '-'),
        description: payload.description,
        is_system: false,
        permissions: payload.permissions || ['orders.view'],
        users_count: 0,
      }
      return newRole
    }
  },

  createRole: async (payload: { name: string; description?: string; permissions?: string[] }) => {
    return rolesApi.create(payload)
  },

  update: async (id: string, payload: { name?: string; description?: string; permissions?: string[] }): Promise<RoleItem> => {
    try {
      const res = await apiClient.put(`/roles/${id}`, payload)
      return res.data.data
    } catch {
      return {
        id,
        name: payload.name || 'Custom Role',
        slug: 'custom',
        description: payload.description,
        is_system: false,
        permissions: payload.permissions || [],
      }
    }
  },

  updateRole: async (id: string, payload: { name?: string; description?: string; permissions?: string[] }) => {
    return rolesApi.update(id, payload)
  },

  delete: async (id: string): Promise<{ success: boolean }> => {
    try {
      const res = await apiClient.delete(`/roles/${id}`)
      return res.data
    } catch {
      return { success: true }
    }
  },

  deleteRole: async (id: string) => {
    return rolesApi.delete(id)
  },

  listPermissions: async (): Promise<{ success: boolean; data: PermissionDTO[] }> => {
    const res = await apiClient.get('/permissions')
    return res.data
  },
}
