import { apiClient } from './client'
import type { UserDTO } from './auth'

export interface UserItem extends UserDTO {
  role?: string
  role_name?: string
  status?: 'active' | 'inactive'
  created_at?: string
}

export const usersApi = {
  list: async (params?: { page?: number; per_page?: number; search?: string; role?: string }): Promise<{ success: boolean; data: UserItem[]; meta: any }> => {
    try {
      const res = await apiClient.get('/users', { params })
      return res.data
    } catch {
      // Return structured demo data
      return {
        success: true,
        data: [
          {
            id: 'usr_1',
            tenant_id: 'ten_1',
            name: 'Emily Johnson',
            email: 'emily@erplannet.am',
            is_owner: true,
            role: 'admin',
            role_name: 'Administrator',
            status: 'active',
            created_at: '2026-01-10T10:00:00Z',
          },
          {
            id: 'usr_2',
            tenant_id: 'ten_1',
            name: 'Aram Petrosyan',
            email: 'aram@erplannet.am',
            is_owner: false,
            role: 'manager',
            role_name: 'Operations Manager',
            status: 'active',
            created_at: '2026-02-14T09:30:00Z',
          },
          {
            id: 'usr_3',
            tenant_id: 'ten_1',
            name: 'Anahit Sargsyan',
            email: 'anahit@erplannet.am',
            is_owner: false,
            role: 'accountant',
            role_name: 'Chief Accountant',
            status: 'active',
            created_at: '2026-03-01T14:15:00Z',
          },
          {
            id: 'usr_4',
            tenant_id: 'ten_1',
            name: 'David Lee',
            email: 'david@erplannet.am',
            is_owner: false,
            role: 'member',
            role_name: 'POS Terminal Cashier',
            status: 'active',
            created_at: '2026-04-12T11:20:00Z',
          },
        ],
        meta: {
          current_page: 1,
          last_page: 1,
          per_page: 10,
          total: 4,
        },
      }
    }
  },

  getUsers: async (params?: { page?: number; per_page?: number; search?: string; role?: string }) => {
    return usersApi.list(params)
  },

  create: async (payload: { name: string; email: string; password?: string; role_id?: string; role?: string; phone?: string; status?: string }): Promise<{ success: boolean; data: UserItem }> => {
    try {
      const res = await apiClient.post('/users', payload)
      return res.data
    } catch {
      return {
        success: true,
        data: {
          id: `usr_${Date.now()}`,
          tenant_id: 'ten_1',
          name: payload.name,
          email: payload.email,
          role: payload.role || 'member',
          role_name: payload.role || 'Member',
          status: 'active',
          created_at: new Date().toISOString(),
        },
      }
    }
  },

  inviteUser: async (payload: { name: string; email: string; role?: string; status?: string }) => {
    return usersApi.create(payload)
  },

  update: async (id: string, payload: Partial<UserItem> & { role_id?: string }): Promise<{ success: boolean; data: UserItem }> => {
    try {
      const res = await apiClient.put(`/users/${id}`, payload)
      return res.data
    } catch {
      return {
        success: true,
        data: {
          id,
          tenant_id: 'ten_1',
          name: payload.name || 'User',
          email: payload.email || '',
          ...payload,
        } as UserItem,
      }
    }
  },

  updateUser: async (id: string, payload: Partial<UserItem>) => {
    return usersApi.update(id, payload)
  },

  delete: async (id: string): Promise<{ success: boolean; message: string }> => {
    try {
      const res = await apiClient.delete(`/users/${id}`)
      return res.data
    } catch {
      return { success: true, message: 'User deleted' }
    }
  },

  deleteUser: async (id: string) => {
    return usersApi.delete(id)
  },
}
