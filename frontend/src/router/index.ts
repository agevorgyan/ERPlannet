import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useTenantStore } from '@/stores/tenantStore'
import AppLayout from '@/layouts/AppLayout.vue'

const routes: RouteRecordRaw[] = [
  // Authentication Routes (Guest Only)
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/views/auth/RegisterView.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: () => import('@/views/auth/ForgotPasswordView.vue'),
    meta: { guestOnly: true },
  },

  // Onboarding Wizard
  {
    path: '/onboarding',
    name: 'onboarding',
    component: () => import('@/views/onboarding/OnboardingView.vue'),
    meta: { requiresAuth: true },
  },

  // Protected App Shell Routes (Using AppLayout)
  {
    path: '/',
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/dashboard',
      },
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('@/views/dashboard/DashboardView.vue'),
      },
      {
        path: 'settings',
        name: 'settings',
        component: () => import('@/views/settings/TenantSettingsView.vue'),
        meta: { permission: 'settings.view' },
      },
      {
        path: 'settings/users',
        name: 'settings-users',
        component: () => import('@/views/settings/UsersView.vue'),
        meta: { permission: 'users.view' },
      },
      {
        path: 'settings/roles',
        name: 'settings-roles',
        component: () => import('@/views/settings/RolesView.vue'),
        meta: { permission: 'roles.view' },
      },
      {
        path: 'settings/billing',
        name: 'settings-billing',
        component: () => import('@/views/settings/BillingView.vue'),
        meta: { permission: 'billing.view' },
      },
    ],
  },

  // Fallback 404
  {
    path: '/:pathMatch(.*)*',
    redirect: '/dashboard',
  },
]

export const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

// Navigation Guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  const tenantStore = useTenantStore()

  // Ensure tenant context is initialized
  if (!tenantStore.isTenantLoaded) {
    try {
      await tenantStore.fetchTenantInfo()
    } catch {
      // Ignored fallback
    }
  }

  const isAuthenticated = authStore.isAuthenticated

  // Check guest-only routes
  if (to.meta.guestOnly && isAuthenticated) {
    return next({ path: '/dashboard' })
  }

  // Check requiresAuth routes
  if (to.meta.requiresAuth && !isAuthenticated) {
    return next({
      path: '/login',
      query: { redirect: to.fullPath },
    })
  }

  // Check RBAC permission if route demands one
  if (to.meta.permission && typeof to.meta.permission === 'string') {
    if (!authStore.hasPermission(to.meta.permission)) {
      // User does not possess permission for this route
      return next({ path: '/dashboard' })
    }
  }

  next()
})
