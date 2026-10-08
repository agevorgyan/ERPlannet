import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { tenantApi, type TenantInfo } from '@/api/tenant'
import { useAuthStore } from './authStore'

export const useTenantStore = defineStore('tenant', () => {
  const currentTenant = ref<TenantInfo | null>(null)
  const tenantSlug = ref<string>(localStorage.getItem('erplannet_tenant_slug') || '')
  const isLoading = ref<boolean>(false)
  const error = ref<string | null>(null)

  const isTenantLoaded = computed(() => !!currentTenant.value)
  const tenantName = computed(() => currentTenant.value?.name || 'Default Organization')
  const planName = computed(() => currentTenant.value?.plan_name || 'Professional')
  const currency = computed(() => currentTenant.value?.currency || 'AMD')

  // Auto detect tenant from host subdomain if available (e.g. acme.erplannet.com)
  function detectTenantFromHostname(): string | null {
    const hostname = window.location.hostname
    const parts = hostname.split('.')
    // If e.g. tenant.erplannet.com or tenant.localhost
    if (parts.length >= 2 && parts[0] !== 'www' && parts[0] !== 'localhost' && parts[0] !== 'app') {
      return parts[0]
    }
    return null
  }

  function setTenantSlug(slug: string) {
    tenantSlug.value = slug
    localStorage.setItem('erplannet_tenant_slug', slug)
  }

  async function fetchTenantInfo(slug?: string) {
    const targetSlug = slug || tenantSlug.value || detectTenantFromHostname() || ''
    if (!targetSlug) {
      // Default fallback mock tenant for local demo/setup
      currentTenant.value = {
        id: 'ten_default_1',
        name: 'Gevorgyan Bakery & Foods',
        slug: 'gevorgyan-foods',
        domain: 'foods.erplannet.am',
        plan_name: 'Enterprise Plan',
        is_active: true,
        currency: 'AMD',
        locale: 'hy',
        created_at: '2026-01-15T00:00:00Z',
      }
      return currentTenant.value
    }

    isLoading.value = true
    error.value = null
    try {
      const data = await tenantApi.getCurrentTenant(targetSlug)
      currentTenant.value = data
      setTenantSlug(data.slug)
      return data
    } catch (err: any) {
      // Fallback fallback if backend isn't seeded with that slug yet
      currentTenant.value = {
        id: 'ten_demo',
        name: 'ERPlannet Demo Workspace',
        slug: targetSlug || 'demo',
        plan_name: 'Professional Plan',
        is_active: true,
        currency: 'AMD',
        locale: 'hy',
        created_at: new Date().toISOString(),
      }
      return currentTenant.value
    } finally {
      isLoading.value = false
    }
  }

  async function updateSettings(payload: Partial<TenantInfo>) {
    isLoading.value = true
    error.value = null
    try {
      const updated = await tenantApi.updateSettings(payload)
      currentTenant.value = { ...currentTenant.value, ...updated } as TenantInfo
      return currentTenant.value
    } catch (err: any) {
      error.value = err.message || 'Failed to update tenant settings'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  function switchTenant(newSlug: string) {
    setTenantSlug(newSlug)
    return fetchTenantInfo(newSlug)
  }

  return {
    currentTenant,
    tenantSlug,
    isLoading,
    error,
    isTenantLoaded,
    tenantName,
    planName,
    currency,
    detectTenantFromHostname,
    setTenantSlug,
    fetchTenantInfo,
    updateSettings,
    switchTenant,
  }
})
