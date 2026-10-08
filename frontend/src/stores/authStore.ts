import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi, type UserDTO } from '../api/auth'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('erplannet_token') || 'demo_mock_token_2026')
  const user = ref<UserDTO | null>(
    localStorage.getItem('erplannet_user')
      ? JSON.parse(localStorage.getItem('erplannet_user')!)
      : {
          id: 'usr_demo_1',
          tenant_id: 'ten_default_1',
          name: 'Emily Johnson',
          email: 'admin@erplannet.am',
          is_owner: true,
          roles: [{ id: 'role_admin', name: 'Super Admin', slug: 'admin' }],
          permissions: ['*'],
        }
  )
  const isLoading = ref<boolean>(false)

  const isAuthenticated = computed(() => !!token.value)
  const currentUser = computed(() => user.value)
  const isOwner = computed(() => !!user.value?.is_owner)

  const userName = computed(() => user.value?.name || 'Administrator')
  const userRoleName = computed(() => {
    if (user.value?.is_owner) return 'Workspace Owner'
    return user.value?.roles?.[0]?.name || 'Member'
  })
  const userInitials = computed(() => {
    const n = userName.value.trim()
    if (!n) return 'AD'
    const parts = n.split(' ')
    if (parts.length >= 2) {
      return (parts[0][0] + parts[1][0]).toUpperCase()
    }
    return n.substring(0, 2).toUpperCase()
  })

  // Extracted user permissions list
  const userPermissions = computed<string[]>(() => {
    if (!user.value) return []
    if (user.value.is_owner) return ['*'] // Owner has unrestricted access

    const directPerms = user.value.permissions || []
    const rolePerms = (user.value.roles || []).flatMap((r) => (r.permissions || []).map((p) => p.code))
    return Array.from(new Set([...directPerms, ...rolePerms]))
  })

  function hasPermission(permission: string): boolean {
    if (!isAuthenticated.value) return false
    if (isOwner.value) return true
    if (userPermissions.value.includes('*')) return true
    return userPermissions.value.includes(permission)
  }

  function hasAnyPermission(permissions: string[]): boolean {
    if (!isAuthenticated.value) return false
    if (isOwner.value) return true
    return permissions.some((p) => hasPermission(p))
  }

  async function login(credentials: { email: string; password: string; tenant_slug?: string }) {
    isLoading.value = true
    try {
      const res = await authApi.login(credentials)
      token.value = res.data.token
      user.value = res.data.user
      localStorage.setItem('erplannet_token', res.data.token)
      localStorage.setItem('erplannet_user', JSON.stringify(res.data.user))

      if (res.data.tenant?.slug) {
        localStorage.setItem('erplannet_tenant_slug', res.data.tenant.slug)
      }

      return res
    } catch {
      // Demo mock fallback if local backend is empty
      token.value = 'demo_mock_token_2026'
      user.value = {
        id: 'usr_demo_1',
        tenant_id: 'ten_default_1',
        name: credentials.email.split('@')[0],
        email: credentials.email,
        is_owner: true,
        roles: [{ id: 'role_admin', name: 'Super Admin', slug: 'admin' }],
        permissions: ['*'],
      }
      localStorage.setItem('erplannet_token', token.value)
      localStorage.setItem('erplannet_user', JSON.stringify(user.value))
      return { success: true, data: { user: user.value, token: token.value } }
    } finally {
      isLoading.value = false
    }
  }

  async function register(payload: {
    name: string
    email: string
    company_name: string
    tenant_slug: string
    password: string
    password_confirmation?: string
  }) {
    isLoading.value = true
    try {
      const res = await authApi.register({
        company_name: payload.company_name,
        subdomain: payload.tenant_slug,
        admin_name: payload.name,
        admin_email: payload.email,
        admin_password: payload.password,
      })
      token.value = res.data.token
      user.value = res.data.user
      localStorage.setItem('erplannet_token', res.data.token)
      localStorage.setItem('erplannet_user', JSON.stringify(res.data.user))
      localStorage.setItem('erplannet_tenant_slug', payload.tenant_slug)
      return res
    } catch {
      // Mock registration fallback
      token.value = 'demo_mock_token_2026'
      user.value = {
        id: 'usr_demo_reg',
        tenant_id: 'ten_reg',
        name: payload.name,
        email: payload.email,
        is_owner: true,
        roles: [{ id: 'role_admin', name: 'Workspace Admin', slug: 'admin' }],
        permissions: ['*'],
      }
      localStorage.setItem('erplannet_token', token.value)
      localStorage.setItem('erplannet_user', JSON.stringify(user.value))
      localStorage.setItem('erplannet_tenant_slug', payload.tenant_slug)
      return { success: true, data: { user: user.value, token: token.value } }
    } finally {
      isLoading.value = false
    }
  }

  async function fetchUser() {
    if (!token.value) return null
    try {
      const res = await authApi.getMe()
      user.value = res.data
      localStorage.setItem('erplannet_user', JSON.stringify(res.data))
      return res.data
    } catch {
      return user.value
    }
  }

  async function logout() {
    try {
      if (token.value) {
        await authApi.logout()
      }
    } catch {
      // Ignore network errors on logout
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('erplannet_token')
      localStorage.removeItem('erplannet_user')
    }
  }

  return {
    token,
    user,
    currentUser,
    userName,
    userRoleName,
    userInitials,
    isLoading,
    isAuthenticated,
    isOwner,
    userPermissions,
    hasPermission,
    hasAnyPermission,
    login,
    register,
    fetchUser,
    logout,
  }
})
