<script setup lang="ts">
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import {
  DollarSign,
  TrendingUp,
  Users,
  Activity,
  Layers,
  Calendar,
  CheckCircle2,
  Clock,
  AlertCircle,
  ArrowRight,
  Sparkles,
  ArrowUpRight,
  ArrowDownRight,
  Plus,
  RefreshCw,
  ShoppingBag,
  Package,
  FileText,
  CreditCard,
  Shield,
  Bot,
} from 'lucide-vue-next'

import AppCard from '@/components/ui/AppCard.vue'
import AppStatCard from '@/components/ui/AppStatCard.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { useAuthStore } from '@/stores/authStore'
import { useTenantStore } from '@/stores/tenantStore'
import { useUiStore } from '@/stores/uiStore'

const { t } = useI18n()
const authStore = useAuthStore()
const tenantStore = useTenantStore()
const uiStore = useUiStore()

const dateRange = ref('May 1 – May 31, 2026')
const isRefreshing = ref(false)

async function refreshData() {
  isRefreshing.value = true
  setTimeout(() => {
    isRefreshing.value = false
    uiStore.success('Dashboard metrics refreshed')
  }, 500)
}

// Quick links matching image 1
const quickLinks = [
  { label: 'Users & Team', icon: Users, to: '/settings/users', color: 'text-blue-600 bg-blue-50' },
  { label: 'Role Policies', icon: Shield, to: '/settings/roles', color: 'text-indigo-600 bg-indigo-50' },
  { label: 'Organization', icon: Layers, to: '/settings', color: 'text-emerald-600 bg-emerald-50' },
  { label: 'Billing & Plan', icon: CreditCard, to: '/settings/billing', color: 'text-purple-600 bg-purple-50' },
  { label: 'Audit Log', icon: FileText, to: '/settings', color: 'text-amber-600 bg-amber-50' },
  { label: 'AI Workspace', icon: Bot, to: '/dashboard', color: 'text-rose-600 bg-rose-50' },
]

// Recent system activities feed
const recentActivities = [
  {
    id: 1,
    title: 'POS #PO-1245 checkout processed',
    author: 'David Lee',
    time: '1h ago',
    badge: 'Completed',
    badgeVariant: 'success' as const,
  },
  {
    id: 2,
    title: 'New user Sarah Johnson added to Sales',
    author: 'Aram Petrosyan',
    time: '3h ago',
    badge: 'User Added',
    badgeVariant: 'brand' as const,
  },
  {
    id: 3,
    title: 'Role "Inventory Auditor" updated',
    author: 'Admin',
    time: '5h ago',
    badge: 'Security',
    badgeVariant: 'warning' as const,
  },
  {
    id: 4,
    title: 'Monthly subscription auto-renewed',
    author: 'Stripe Gateway',
    time: '6h ago',
    badge: 'Billing',
    badgeVariant: 'purple' as const,
  },
]

// Smart Operational Tasks
const operationalTasks = [
  { id: 1, title: 'Review Q2 Budget & Expense Allocations', dept: 'Finance', date: 'May 25', priority: 'High', priorityVariant: 'danger' as const },
  { id: 2, title: 'Inventory Batch Quality Verification', dept: 'Operations', date: 'May 27', priority: 'Medium', priorityVariant: 'warning' as const },
  { id: 3, title: 'Onboard 3 New Factory Dispatchers', dept: 'HR & Team', date: 'May 28', priority: 'High', priorityVariant: 'danger' as const },
  { id: 4, title: 'Supplier Contract Price Audit', dept: 'Procurement', date: 'May 30', priority: 'Low', priorityVariant: 'neutral' as const },
]
</script>

<template>
  <div class="space-y-6 text-left">
    <!-- Top Welcome Banner & Date Range Header (SoftFire & EduNova style) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
          <span>Welcome back, {{ authStore.userName }}!</span>
          <span class="inline-block animate-wave text-xl">👋</span>
        </h1>
        <p class="text-xs text-slate-500 mt-0.5">
          Here is what is happening across <strong class="font-semibold text-slate-700">{{ tenantStore.tenantName }}</strong> today.
        </p>
      </div>

      <div class="flex items-center gap-2.5">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200/90 rounded-xl text-xs font-semibold text-slate-700 shadow-2xs">
          <Calendar class="w-3.5 h-3.5 text-slate-400" />
          <span>{{ dateRange }}</span>
        </div>

        <AppButton
          variant="outline"
          size="sm"
          :loading="isRefreshing"
          @click="refreshData"
        >
          <template #leading>
            <RefreshCw class="w-3.5 h-3.5" :class="isRefreshing ? 'animate-spin' : ''" />
          </template>
          Refresh
        </AppButton>
      </div>
    </div>

    <!-- Hero Promotional Banner (EduNova AI ERP 2026 style) -->
    <div class="relative overflow-hidden rounded-3xl bg-linear-to-r from-blue-600 via-brand-600 to-indigo-700 text-white p-6 sm:p-8 shadow-xl shadow-brand-500/15">
      <div class="relative z-10 max-w-2xl">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-white text-xs font-bold mb-3 border border-white/20">
          <Sparkles class="w-3.5 h-3.5 text-amber-300" />
          <span>ERPlannet Enterprise Core</span>
        </div>
        <h2 class="text-xl sm:text-2xl font-black tracking-tight leading-snug">
          Empower operations. Automate your multi-tenant workflows.
        </h2>
        <p class="text-xs sm:text-sm text-blue-100 mt-2 leading-relaxed max-w-xl">
          Your organization is currently operating on the {{ tenantStore.planName }}. All RBAC security policies, audit logs, and multi-tenant data pipelines are active.
        </p>

        <div class="mt-5 flex flex-wrap items-center gap-3">
          <router-link
            to="/settings/users"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-brand-700 text-xs font-bold shadow-md hover:bg-blue-50 transition-colors"
          >
            <span>Manage Team Members</span>
            <ArrowRight class="w-3.5 h-3.5" />
          </router-link>
          <router-link
            to="/settings/billing"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/15 hover:bg-white/25 text-white text-xs font-bold border border-white/20 transition-colors"
          >
            <span>Billing Overview</span>
          </router-link>
        </div>
      </div>

      <!-- Background Glow Orbs -->
      <div class="absolute -right-10 -bottom-10 w-72 h-72 rounded-full bg-white/10 blur-2xl pointer-events-none" />
      <div class="absolute right-32 -top-10 w-48 h-48 rounded-full bg-brand-400/20 blur-xl pointer-events-none" />
    </div>

    <!-- 4 StatCards Row with SVG Sparklines (matching reference images) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <AppStatCard
        title="Total Revenue"
        value="֏ 12,460,800"
        subtitle="Current billing cycle"
        trend="12.6%"
        trend-type="up"
        icon-bg="bg-emerald-50"
        icon-color="text-emerald-600"
        sparkline-color="#10b981"
        :sparkline-points="[10, 14, 18, 16, 22, 28, 25, 34]"
      >
        <template #icon>
          <DollarSign class="w-5 h-5 stroke-[2.2]" />
        </template>
      </AppStatCard>

      <AppStatCard
        title="Active Team Members"
        value="128"
        subtitle="Across 4 departments"
        trend="6.2%"
        trend-type="up"
        icon-bg="bg-brand-50"
        icon-color="text-brand-600"
        sparkline-color="#2563eb"
        :sparkline-points="[45, 52, 60, 75, 88, 105, 120, 128]"
      >
        <template #icon>
          <Users class="w-5 h-5 stroke-[2.2]" />
        </template>
      </AppStatCard>

      <AppStatCard
        title="Open Tasks & Orders"
        value="24"
        subtitle="12 pending approvals"
        trend="3.4%"
        trend-type="down"
        icon-bg="bg-amber-50"
        icon-color="text-amber-600"
        sparkline-color="#f59e0b"
        :sparkline-points="[30, 28, 26, 32, 29, 25, 27, 24]"
      >
        <template #icon>
          <Activity class="w-5 h-5 stroke-[2.2]" />
        </template>
      </AppStatCard>

      <AppStatCard
        title="System Health"
        value="99.98%"
        subtitle="Multi-tenant cluster latency 18ms"
        trend="Optimal"
        trend-type="up"
        icon-bg="bg-indigo-50"
        icon-color="text-indigo-600"
        sparkline-color="#6366f1"
        :sparkline-points="[99, 99.5, 99.7, 99.8, 99.9, 99.95, 99.98, 99.98]"
      >
        <template #icon>
          <CheckCircle2 class="w-5 h-5 stroke-[2.2]" />
        </template>
      </AppStatCard>
    </div>

    <!-- Quick Links Bar (Image 1 EduNova style) -->
    <AppCard>
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-sm font-bold text-slate-800">Quick Operations</h3>
          <p class="text-xs text-slate-400">Frequently used shortcuts across your ERP workspace</p>
        </div>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <router-link
          v-for="link in quickLinks"
          :key="link.label"
          :to="link.to"
          class="flex flex-col items-center justify-center p-4 rounded-2xl border border-slate-100 hover:border-brand-200 hover:bg-slate-50/60 transition-all text-center group"
        >
          <div :class="['w-11 h-11 rounded-2xl flex items-center justify-center mb-2.5 transition-transform group-hover:scale-110', link.color]">
            <component :is="link.icon" class="w-5 h-5" />
          </div>
          <span class="text-xs font-bold text-slate-700 group-hover:text-brand-600 transition-colors">
            {{ link.label }}
          </span>
        </router-link>
      </div>
    </AppCard>

    <!-- Visual Charts Row: Dual Curve Revenue & Workflow Status Donut (Image 2 style) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Revenue vs Expenses Line Chart Widget -->
      <div class="lg:col-span-2">
        <AppCard
          title="Revenue vs Operational Expenses"
          subtitle="Financial overview for the current fiscal quarter"
        >
          <template #actions>
            <div class="flex items-center gap-3 text-xs">
              <span class="inline-flex items-center gap-1.5 font-semibold text-brand-600">
                <span class="w-2.5 h-2.5 rounded-full bg-brand-600" /> Revenue
              </span>
              <span class="inline-flex items-center gap-1.5 font-semibold text-amber-500">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500" /> Expenses
              </span>
            </div>
          </template>

          <!-- SVG Visual Chart Simulation -->
          <div class="relative h-64 w-full pt-4">
            <svg viewBox="0 0 600 200" class="w-full h-full overflow-visible">
              <!-- Grid lines -->
              <line x1="0" y1="40" x2="600" y2="40" stroke="#f1f5f9" stroke-dasharray="4" />
              <line x1="0" y1="90" x2="600" y2="90" stroke="#f1f5f9" stroke-dasharray="4" />
              <line x1="0" y1="140" x2="600" y2="140" stroke="#f1f5f9" stroke-dasharray="4" />
              <line x1="0" y1="190" x2="600" y2="190" stroke="#e2e8f0" />

              <!-- Revenue Line (Blue) -->
              <path
                d="M 20 160 Q 100 120, 180 80 T 340 70 T 480 40 T 580 30"
                fill="none"
                stroke="#2563eb"
                stroke-width="3.5"
                stroke-linecap="round"
              />

              <!-- Expenses Line (Amber) -->
              <path
                d="M 20 175 Q 100 150, 180 130 T 340 120 T 480 90 T 580 80"
                fill="none"
                stroke="#f59e0b"
                stroke-width="3"
                stroke-linecap="round"
              />

              <!-- Data Point Indicators -->
              <circle cx="340" cy="70" r="5" fill="#2563eb" stroke="#ffffff" stroke-width="2" />
              <circle cx="480" cy="40" r="5" fill="#2563eb" stroke="#ffffff" stroke-width="2" />
              <circle cx="580" cy="30" r="6" fill="#2563eb" stroke="#ffffff" stroke-width="2.5" />
            </svg>

            <!-- Chart Horizontal Axis Labels -->
            <div class="flex justify-between text-[11px] text-slate-400 mt-2 font-medium">
              <span>May 1</span>
              <span>May 8</span>
              <span>May 15</span>
              <span>May 22</span>
              <span>May 31</span>
            </div>
          </div>
        </AppCard>
      </div>

      <!-- Operations Status Donut Chart (Image 2 style) -->
      <div class="lg:col-span-1">
        <AppCard
          title="Workflow Status"
          subtitle="Distribution of current operational jobs"
        >
          <div class="flex flex-col items-center justify-center py-4">
            <!-- Simulated Donut Ring -->
            <div class="relative w-40 h-40 flex items-center justify-center">
              <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                <!-- Background track -->
                <circle cx="50" cy="50" r="40" fill="transparent" stroke="#f1f5f9" stroke-width="14" />
                <!-- Completed (Emerald) 40% -->
                <circle cx="50" cy="50" r="40" fill="transparent" stroke="#10b981" stroke-width="14" stroke-dasharray="100 251" stroke-dashoffset="0" />
                <!-- In Progress (Brand Blue) 30% -->
                <circle cx="50" cy="50" r="40" fill="transparent" stroke="#2563eb" stroke-width="14" stroke-dasharray="75 251" stroke-dashoffset="-100" />
                <!-- Pending Review (Amber) 20% -->
                <circle cx="50" cy="50" r="40" fill="transparent" stroke="#f59e0b" stroke-width="14" stroke-dasharray="50 251" stroke-dashoffset="-175" />
              </svg>
              <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Total</span>
                <span class="text-2xl font-black text-slate-900 leading-none mt-0.5">56</span>
              </div>
            </div>

            <!-- Legend Items -->
            <div class="w-full space-y-2 mt-5 text-xs">
              <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 font-medium text-slate-700">
                  <span class="w-2.5 h-2.5 rounded-full bg-emerald-500" /> Completed
                </span>
                <span class="font-bold text-slate-900">20 (35.7%)</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 font-medium text-slate-700">
                  <span class="w-2.5 h-2.5 rounded-full bg-brand-600" /> In Progress
                </span>
                <span class="font-bold text-slate-900">18 (32.1%)</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 font-medium text-slate-700">
                  <span class="w-2.5 h-2.5 rounded-full bg-amber-500" /> Pending Review
                </span>
                <span class="font-bold text-slate-900">10 (17.9%)</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 font-medium text-slate-700">
                  <span class="w-2.5 h-2.5 rounded-full bg-slate-300" /> On Hold
                </span>
                <span class="font-bold text-slate-900">8 (14.3%)</span>
              </div>
            </div>
          </div>
        </AppCard>
      </div>
    </div>

    <!-- Lower Section: Team Tasks Table & Recent Activities Feed -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Team Operational Tasks (2 cols) -->
      <div class="lg:col-span-2">
        <AppCard
          title="Team Operational Priorities"
          subtitle="Assigned tasks and milestones across departments"
          no-padding
        >
          <div class="divide-y divide-slate-100">
            <div
              v-for="task in operationalTasks"
              :key="task.id"
              class="p-4 flex items-center justify-between gap-4 hover:bg-slate-50/70 transition-colors"
            >
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-xs shrink-0">
                  {{ task.dept.substring(0, 2).toUpperCase() }}
                </div>
                <div>
                  <h4 class="text-sm font-bold text-slate-800">{{ task.title }}</h4>
                  <div class="flex items-center gap-2 text-xs text-slate-400 mt-0.5">
                    <span>{{ task.dept }}</span>
                    <span>&bull;</span>
                    <span>Due {{ task.date }}</span>
                  </div>
                </div>
              </div>

              <AppBadge :variant="task.priorityVariant" size="sm">
                {{ task.priority }} Priority
              </AppBadge>
            </div>
          </div>
        </AppCard>
      </div>

      <!-- Recent Activities Feed (1 col) -->
      <div class="lg:col-span-1">
        <AppCard
          title="Recent Platform Activity"
          subtitle="Audit logs and system events"
          no-padding
        >
          <div class="divide-y divide-slate-100">
            <div
              v-for="act in recentActivities"
              :key="act.id"
              class="p-4 hover:bg-slate-50/70 transition-colors"
            >
              <div class="flex items-center justify-between mb-1">
                <AppBadge :variant="act.badgeVariant" size="sm">
                  {{ act.badge }}
                </AppBadge>
                <span class="text-[11px] text-slate-400">{{ act.time }}</span>
              </div>
              <p class="text-xs font-semibold text-slate-800 mt-1">
                {{ act.title }}
              </p>
              <p class="text-[11px] text-slate-400 mt-0.5">
                by {{ act.author }}
              </p>
            </div>
          </div>
        </AppCard>
      </div>
    </div>
  </div>
</template>
