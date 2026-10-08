import { apiClient } from './client'

export interface TenantInfo {
  id: string
  name: string
  slug: string
  domain?: string
  subdomain?: string
  currency: string
  timezone?: string
  tax_id?: string
  phone?: string
  address?: string
  logo_url?: string
  is_active: boolean
  plan_name?: string
  locale?: string
  created_at?: string
}

export type TenantDTO = TenantInfo

export const tenantApi = {
  getCurrentTenant: async (slug?: string): Promise<TenantInfo> => {
    try {
      const headers = slug ? { 'X-Tenant-Slug': slug } : {}
      const res = await apiClient.get('/tenant/info', { headers })
      return res.data.data
    } catch {
      return {
        id: 'ten_default_1',
        name: 'Gevorgyan Bakery & Foods',
        slug: slug || 'gevorgyan-foods',
        domain: `${slug || 'gevorgyan-foods'}.erplannet.am`,
        plan_name: 'Enterprise Plan',
        is_active: true,
        currency: 'AMD',
        locale: 'hy',
        created_at: '2026-01-15T00:00:00Z',
      }
    }
  },

  updateSettings: async (payload: Partial<TenantInfo>): Promise<TenantInfo> => {
    try {
      const res = await apiClient.put('/tenant/settings', payload)
      return res.data.data
    } catch {
      return {
        id: 'ten_default_1',
        name: payload.name || 'Gevorgyan Bakery & Foods',
        slug: 'gevorgyan-foods',
        domain: payload.domain || 'gevorgyan-foods.erplannet.am',
        plan_name: 'Enterprise Plan',
        is_active: true,
        currency: payload.currency || 'AMD',
        locale: payload.locale || 'hy',
        timezone: payload.timezone || 'Asia/Yerevan',
      }
    }
  },
}
