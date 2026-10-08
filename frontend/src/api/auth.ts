import { apiClient } from './client'

export interface UserDTO {
  id: string
  tenant_id: string
  name: string
  email: string
  phone?: string
  is_owner?: boolean
  roles?: Array<{ id: string; name: string; slug: string; permissions?: Array<{ code: string }> }>
  permissions?: string[]
  avatar_url?: string
}

export interface AuthResponse {
  success: boolean
  message: string
  data: {
    user: UserDTO
    token: string
    tenant?: {
      id: string
      name: string
      slug: string
      currency: string
    }
  }
}

export const authApi = {
  login: async (credentials: { email: string; password: string; tenant_slug?: string }): Promise<AuthResponse> => {
    const res = await apiClient.post<AuthResponse>('/auth/login', credentials, {
      headers: credentials.tenant_slug ? { 'X-Tenant-Slug': credentials.tenant_slug } : {},
    })
    return res.data
  },

  register: async (payload: {
    company_name: string
    subdomain: string
    admin_name: string
    admin_email: string
    admin_password: string
    currency?: string
  }): Promise<AuthResponse> => {
    const res = await apiClient.post<AuthResponse>('/auth/register', payload)
    return res.data
  },

  getMe: async (): Promise<{ success: boolean; data: UserDTO }> => {
    const res = await apiClient.get('/auth/me')
    return res.data
  },

  logout: async (): Promise<{ success: boolean; message: string }> => {
    const res = await apiClient.post('/auth/logout')
    return res.data
  },

  forgotPassword: async (email: string): Promise<{ success: boolean; message: string }> => {
    const res = await apiClient.post('/auth/forgot-password', { email })
    return res.data
  },

  resetPassword: async (payload: { token: string; email: string; password: string }): Promise<{ success: boolean; message: string }> => {
    const res = await apiClient.post('/auth/reset-password', payload)
    return res.data
  },
}
