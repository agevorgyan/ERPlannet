<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import {
  LayoutDashboard,
  Users,
  ShieldCheck,
  Building2,
  CreditCard,
  Settings,
  Bell,
  Search,
  ChevronDown,
  LogOut,
  Menu,
  X,
  Sparkles,
  Globe,
  ExternalLink,
  ChevronLeft,
  ChevronRight,
  Layers,
  ArrowRight,
  CheckCircle2,
} from 'lucide-vue-next'

import { useAuthStore } from '@/stores/authStore'
import { useTenantStore } from '@/stores/tenantStore'
import { useUiStore } from '@/stores/uiStore'
import AppToast from '@/components/ui/AppToast.vue'
import AppConfirmDialog from '@/components/ui/AppConfirmDialog.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppButton from '@/components/ui/AppButton.vue'

const router = useRouter()
const route = useRoute()
const { t, locale } = useI18n()

const authStore = useAuthStore()
const tenantStore = useTenantStore()
const uiStore = useUiStore()

const userMenuOpen = ref(false)
const langMenuOpen = ref(false)
const searchQuery = ref('')

// Navigation items filtered by RBAC permissions
const navItems = computed(() => [
  {
    name: 'dashboard',
    label: t('nav.dashboard'),
    to: '/dashboard',
    icon: LayoutDashboard,
    permission: null, // Public to authenticated users
  },
  {
    name: 'users',
    label: t('nav.users'),
    to: '/settings/users',
    icon: Users,
    permission: 'users.view',
  },
  {
    name: 'roles',
    label: t('nav.roles'),
    to: '/settings/roles',
    icon: ShieldCheck,
    permission: 'roles.view',
  },
  {
    name: 'settings',
    label: t('nav.settings'),
    to: '/settings',
    icon: Settings,
    permission: 'settings.view',
  },
  {
    name: 'billing',
    label: t('nav.billing'),
    to: '/settings/billing',
    icon: CreditCard,
    permission: 'billing.view',
  },
])

// Filter by user permissions
const visibleNavItems = computed(() => {
  return navItems.value.filter((item) => {
    if (!item.permission) return true
    return authStore.hasPermission(item.permission)
  })
})

const availableLanguages = [
  { code: 'hy', label: 'Հայերեն', flag: '🇦🇲' },
  { code: 'en', label: 'English', flag: '🇺🇸' },
  { code: 'ru', label: 'Русский', flag: '🇷🇺' },
]

function changeLanguage(langCode: string) {
  locale.value = langCode
  localStorage.setItem('erplannet_locale', langCode)
  langMenuOpen.value = false
  uiStore.success(t('messages.savedSuccess'))
}

function handleLogout() {
  uiStore.confirm({
    title: t('auth.logoutConfirmTitle'),
    message: t('auth.logoutConfirmMessage'),
    confirmText: t('nav.logout'),
    cancelText: t('common.cancel'),
    type: 'danger',
    onConfirm: async () => {
      await authStore.logout()
      router.push('/login')
      uiStore.info(t('auth.loggedOut'))
    },
  })
}

// Global Cmd+K keyboard shortcut listener
function handleGlobalKeydown(e: KeyboardEvent) {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault()
    uiStore.openSearch()
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleGlobalKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleGlobalKeydown)
})

function handleGlobalSearchNavigate(path: string) {
  uiStore.closeSearch()
  router.push(path)
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 flex flex-col antialiased text-slate-800">
    <!-- Topbar & Main Body Container -->
    <div class="flex flex-1 min-h-screen">
      <!-- Desktop Sidebar -->
      <aside
        :class="[
          'hidden lg:flex flex-col border-r border-slate-200/80 bg-white transition-all duration-200 z-30 select-none',
          uiStore.isSidebarCollapsed ? 'w-20' : 'w-64',
        ]"
      >
        <!-- Sidebar Brand Header -->
        <div class="h-16 flex items-center px-5 border-b border-slate-100 justify-between">
          <router-link to="/dashboard" class="flex items-center gap-3 overflow-hidden">
            <div class="w-10 h-10 rounded-xl bg-linear-to-tr from-brand-600 to-brand-500 flex items-center justify-center text-white shadow-md shadow-brand-500/25 shrink-0">
              <Layers class="w-5 h-5 stroke-[2.2]" />
            </div>
            <div v-if="!uiStore.isSidebarCollapsed" class="flex flex-col min-w-0">
              <span class="font-extrabold text-base tracking-tight text-slate-900 leading-none">
                ERPlannet
              </span>
              <span class="text-[10px] font-semibold text-brand-600 tracking-wider uppercase mt-1">
                SaaS ERP 2026
              </span>
            </div>
          </router-link>

          <button
            type="button"
            class="hidden lg:flex items-center justify-center p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
            @click="uiStore.toggleSidebar"
          >
            <ChevronLeft v-if="!uiStore.isSidebarCollapsed" class="w-4 h-4" />
            <ChevronRight v-else class="w-4 h-4" />
          </button>
        </div>

        <!-- Tenant Badge Card -->
        <div v-if="!uiStore.isSidebarCollapsed" class="px-4 py-3 mx-3 mt-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-700 shadow-2xs shrink-0">
            <Building2 class="w-4 h-4 text-brand-600" />
          </div>
          <div class="flex flex-col min-w-0 text-left">
            <span class="text-xs font-bold text-slate-800 truncate">
              {{ tenantStore.tenantName }}
            </span>
            <span class="text-[10px] text-slate-400 truncate">
              {{ tenantStore.currentTenant?.domain || `${tenantStore.tenantSlug || 'demo'}.erplannet.am` }}
            </span>
          </div>
        </div>

        <!-- Sidebar Navigation Menu -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
          <router-link
            v-for="item in visibleNavItems"
            :key="item.name"
            :to="item.to"
            :class="[
              'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150',
              route.path === item.to || (item.to !== '/dashboard' && route.path.startsWith(item.to))
                ? 'bg-brand-600 text-white shadow-sm shadow-brand-500/25'
                : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80',
              uiStore.isSidebarCollapsed ? 'justify-center px-2' : '',
            ]"
            :title="uiStore.isSidebarCollapsed ? item.label : undefined"
          >
            <component :is="item.icon" class="w-5 h-5 shrink-0 stroke-[2]" />
            <span v-if="!uiStore.isSidebarCollapsed" class="truncate">
              {{ item.label }}
            </span>
          </router-link>
        </nav>

        <!-- Sidebar Bottom Plan Card (matching reference image EduNova/SoftFire) -->
        <div v-if="!uiStore.isSidebarCollapsed" class="p-3 m-3 rounded-2xl bg-linear-to-br from-brand-50 via-indigo-50/50 to-blue-50 border border-brand-100/80 text-left">
          <div class="flex items-center gap-2 text-brand-700 font-bold text-xs mb-1">
            <Sparkles class="w-4 h-4 text-brand-600" />
            <span>{{ tenantStore.planName }}</span>
          </div>
          <p class="text-[11px] text-slate-600 leading-snug mb-3">
            Multi-tenant ERP suite with enterprise controls and analytics.
          </p>
          <router-link
            to="/settings/billing"
            class="w-full inline-flex items-center justify-center gap-1.5 py-1.5 px-3 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors"
          >
            <span>Upgrade Plan</span>
            <ArrowRight class="w-3.5 h-3.5" />
          </router-link>
        </div>
      </aside>

      <!-- Main Layout (Topbar + Content Area) -->
      <div class="flex-1 flex flex-col min-w-0">
        <!-- Top Navigation Bar -->
        <header class="h-16 bg-white border-b border-slate-200/80 sticky top-0 z-20 flex items-center justify-between px-4 sm:px-6">
          <!-- Left: Mobile Toggle & Global Search Bar -->
          <div class="flex items-center gap-3 sm:gap-4 flex-1 max-w-xl">
            <button
              type="button"
              class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100"
              @click="uiStore.toggleMobileDrawer"
            >
              <Menu class="w-5 h-5" />
            </button>

            <!-- Global Search Placeholder (EduNova / SoftFire style) -->
            <button
              type="button"
              class="w-full max-w-md hidden sm:flex items-center justify-between px-3.5 py-2 text-xs text-slate-400 bg-slate-50 hover:bg-slate-100/80 border border-slate-200 rounded-xl transition-colors cursor-pointer text-left"
              @click="uiStore.openSearch"
            >
              <div class="flex items-center gap-2">
                <Search class="w-4 h-4 text-slate-400" />
                <span>{{ t('common.search') }}</span>
              </div>
              <kbd class="px-1.5 py-0.5 text-[10px] font-semibold text-slate-500 bg-white border border-slate-200 rounded-md shadow-2xs">
                ⌘K
              </kbd>
            </button>
          </div>

          <!-- Right: Notifications, Language, Tenant & User Menu -->
          <div class="flex items-center gap-2 sm:gap-3">
            <!-- Language Selector Dropdown -->
            <div class="relative">
              <button
                type="button"
                class="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 rounded-xl hover:bg-slate-100 transition-colors"
                @click="langMenuOpen = !langMenuOpen"
              >
                <Globe class="w-4 h-4 text-slate-400" />
                <span class="uppercase font-bold">{{ locale }}</span>
                <ChevronDown class="w-3.5 h-3.5 text-slate-400" />
              </button>

              <div
                v-if="langMenuOpen"
                class="absolute right-0 mt-2 w-36 bg-white rounded-xl shadow-lg border border-slate-100 py-1 z-30"
                @click.outside="langMenuOpen = false"
              >
                <button
                  v-for="l in availableLanguages"
                  :key="l.code"
                  type="button"
                  class="w-full flex items-center justify-between px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 transition-colors"
                  @click="changeLanguage(l.code)"
                >
                  <span class="flex items-center gap-2">
                    <span>{{ l.flag }}</span>
                    <span>{{ l.label }}</span>
                  </span>
                  <CheckCircle2 v-if="locale === l.code" class="w-3.5 h-3.5 text-brand-600" />
                </button>
              </div>
            </div>

            <!-- Notifications Bell -->
            <button
              type="button"
              class="relative p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
              @click="uiStore.toggleNotifications"
            >
              <Bell class="w-5 h-5" />
              <span
                v-if="uiStore.unreadNotificationsCount > 0"
                class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-white"
              />
            </button>

            <!-- User Menu Dropdown -->
            <div class="relative ml-1 sm:ml-2">
              <button
                type="button"
                class="flex items-center gap-2.5 p-1 rounded-xl hover:bg-slate-100 transition-colors"
                @click="userMenuOpen = !userMenuOpen"
              >
                <div class="w-9 h-9 rounded-xl bg-brand-100 text-brand-700 font-bold flex items-center justify-center text-sm border border-brand-200">
                  {{ authStore.userInitials }}
                </div>
                <div class="hidden md:flex flex-col text-left">
                  <span class="text-xs font-bold text-slate-800 leading-tight">
                    {{ authStore.userName }}
                  </span>
                  <span class="text-[10px] text-slate-400 font-medium">
                    {{ authStore.userRoleName }}
                  </span>
                </div>
                <ChevronDown class="w-3.5 h-3.5 text-slate-400 hidden sm:block" />
              </button>

              <div
                v-if="userMenuOpen"
                class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-30"
                @click="userMenuOpen = false"
              >
                <div class="px-4 py-2.5 border-b border-slate-100 text-left">
                  <p class="text-xs font-bold text-slate-800">
                    {{ authStore.userName }}
                  </p>
                  <p class="text-[11px] text-slate-400 truncate">
                    {{ authStore.currentUser?.email || 'admin@erplannet.am' }}
                  </p>
                  <AppBadge variant="brand" size="sm" class="mt-1.5">
                    {{ authStore.userRoleName }}
                  </AppBadge>
                </div>

                <div class="py-1">
                  <router-link
                    to="/settings"
                    class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 transition-colors text-left"
                  >
                    <Settings class="w-4 h-4 text-slate-400" />
                    <span>{{ t('nav.settings') }}</span>
                  </router-link>
                  <router-link
                    to="/settings/billing"
                    class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 transition-colors text-left"
                  >
                    <CreditCard class="w-4 h-4 text-slate-400" />
                    <span>{{ t('nav.billing') }}</span>
                  </router-link>
                </div>

                <div class="pt-1 border-t border-slate-100">
                  <button
                    type="button"
                    class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors text-left"
                    @click="handleLogout"
                  >
                    <LogOut class="w-4 h-4 text-rose-500" />
                    <span>{{ t('nav.logout') }}</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </header>

        <!-- Main Workspace Content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
          <router-view />
        </main>
      </div>
    </div>

    <!-- Mobile Drawer Navigation -->
    <div v-if="uiStore.isMobileDrawerOpen" class="fixed inset-0 z-50 lg:hidden">
      <div
        class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs"
        @click="uiStore.toggleMobileDrawer"
      />
      <div class="fixed inset-y-0 left-0 w-72 bg-white shadow-2xl flex flex-col z-10">
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-100">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-brand-600 flex items-center justify-center text-white">
              <Layers class="w-5 h-5" />
            </div>
            <span class="font-extrabold text-base text-slate-900">ERPlannet</span>
          </div>
          <button
            type="button"
            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100"
            @click="uiStore.toggleMobileDrawer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
          <router-link
            v-for="item in visibleNavItems"
            :key="item.name"
            :to="item.to"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-100"
            @click="uiStore.toggleMobileDrawer"
          >
            <component :is="item.icon" class="w-5 h-5" />
            <span>{{ item.label }}</span>
          </router-link>
        </nav>

        <div class="p-4 border-t border-slate-100">
          <button
            type="button"
            class="w-full flex items-center justify-center gap-2 py-2 px-3 bg-rose-50 text-rose-600 rounded-xl text-xs font-semibold"
            @click="handleLogout"
          >
            <LogOut class="w-4 h-4" />
            <span>{{ t('nav.logout') }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Global Search Modal (⌘K) -->
    <AppModal
      v-model="uiStore.isSearchOpen"
      size="lg"
      :closable="true"
    >
      <template #header>
        <div class="flex items-center gap-3 w-full pr-6">
          <Search class="w-5 h-5 text-slate-400 shrink-0" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Type a command or search modules..."
            class="w-full text-base bg-transparent border-none outline-none text-slate-800 placeholder:text-slate-400"
            autofocus
          />
        </div>
      </template>

      <div class="space-y-4">
        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
          Quick Navigation
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <button
            v-for="item in visibleNavItems"
            :key="item.name"
            type="button"
            class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:border-brand-200 hover:bg-brand-50/40 text-left transition-colors"
            @click="handleGlobalSearchNavigate(item.to)"
          >
            <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600">
              <component :is="item.icon" class="w-4 h-4" />
            </div>
            <div>
              <div class="text-xs font-bold text-slate-800">{{ item.label }}</div>
              <div class="text-[10px] text-slate-400">{{ item.to }}</div>
            </div>
          </button>
        </div>
      </div>
    </AppModal>

    <!-- Global Toasts -->
    <AppToast />

    <!-- Global Confirm Dialog -->
    <AppConfirmDialog />
  </div>
</template>
