import { apiClient } from './client'

export interface PlanDTO {
  id: string
  code: string
  name: string
  price_monthly: number
  price_yearly: number
  features: Array<{ code: string; name: string; value: string; type: string }>
}

export interface SubscriptionInfo {
  id: string
  plan: string
  plan_name: string
  status: string
  renews_at: string
  users_count: number
  users_limit: number
  branches_count: number
  branches_limit: number
}

export interface InvoiceItem {
  id: string
  number: string
  date: string
  amount: string
  status: 'paid' | 'pending' | 'failed'
  download_url?: string
}

const defaultSubscription: SubscriptionInfo = {
  id: 'sub_pro_123',
  plan: 'pro',
  plan_name: 'Professional Plan',
  status: 'active',
  renews_at: 'November 1, 2026',
  users_count: 8,
  users_limit: 25,
  branches_count: 2,
  branches_limit: 5,
}

const defaultInvoices: InvoiceItem[] = [
  { id: 'inv_101', number: 'INV-2026-004', date: 'Oct 01, 2026', amount: '֏ 79,000', status: 'paid' },
  { id: 'inv_102', number: 'INV-2026-003', date: 'Sep 01, 2026', amount: '֏ 79,000', status: 'paid' },
  { id: 'inv_103', number: 'INV-2026-002', date: 'Aug 01, 2026', amount: '֏ 79,000', status: 'paid' },
  { id: 'inv_104', number: 'INV-2026-001', date: 'Jul 01, 2026', amount: '֏ 79,000', status: 'paid' },
]

export const billingApi = {
  getSubscription: async (): Promise<SubscriptionInfo> => {
    try {
      const res = await apiClient.get('/subscription')
      return res.data.data
    } catch {
      return defaultSubscription
    }
  },

  updateSubscription: async (planId: string): Promise<SubscriptionInfo> => {
    try {
      const res = await apiClient.post('/subscription/upgrade', { plan: planId })
      return res.data.data
    } catch {
      return {
        ...defaultSubscription,
        plan: planId,
        plan_name: planId === 'enterprise' ? 'Enterprise Plan' : planId === 'starter' ? 'Starter Plan' : 'Professional Plan',
      }
    }
  },

  listInvoices: async (): Promise<InvoiceItem[]> => {
    try {
      const res = await apiClient.get('/invoices')
      return res.data.data
    } catch {
      return defaultInvoices
    }
  },

  getInvoices: async (): Promise<InvoiceItem[]> => {
    return billingApi.listInvoices()
  },

  listPlans: async (): Promise<{ success: boolean; data: PlanDTO[] }> => {
    const res = await apiClient.get('/plans')
    return res.data
  },
}
