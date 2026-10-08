<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import {
  CreditCard,
  Check,
  Sparkles,
  Download,
  Calendar,
  Zap,
  ShieldCheck,
  AlertCircle,
} from 'lucide-vue-next'

import AppCard from '@/components/ui/AppCard.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppTable from '@/components/ui/AppTable.vue'
import { billingApi, type SubscriptionInfo, type InvoiceItem } from '@/api/billing'
import { useTenantStore } from '@/stores/tenantStore'
import { useUiStore } from '@/stores/uiStore'

const { t } = useI18n()
const tenantStore = useTenantStore()
const uiStore = useUiStore()

const isLoading = ref(false)
const isUpgrading = ref(false)
const subscription = ref<SubscriptionInfo | null>(null)
const invoices = ref<InvoiceItem[]>([])

const availablePlans = [
  {
    id: 'starter',
    name: 'Starter Plan',
    price: '֏ 29,000',
    period: '/ month',
    description: 'Essential multi-tenant ERP features for small businesses.',
    features: [
      'Up to 5 Team Members',
      '1 Warehouse / Branch',
      'POS & Receipt Printing',
      'Basic Financial Reports',
      'Standard Email Support',
    ],
  },
  {
    id: 'pro',
    name: 'Professional Plan',
    price: '֏ 79,000',
    period: '/ month',
    popular: true,
    description: 'Advanced capabilities with automated supply chain and dispatch.',
    features: [
      'Up to 25 Team Members',
      '5 Warehouses & Branches',
      'Full Delivery Fleet & GPS Dispatch',
      'Manufacturing & BOM Recipes',
      'Custom Role Policies (RBAC)',
      'Priority 24/7 Support',
    ],
  },
  {
    id: 'enterprise',
    name: 'Enterprise Plan',
    price: '֏ 199,000',
    period: '/ month',
    description: 'High-volume production, dedicated tenant database & custom SLA.',
    features: [
      'Unlimited Team Members',
      'Unlimited Branches & POS Terminals',
      'Custom Domain with Auto SSL',
      'Fiscal Provider Cloud Integrations',
      'Dedicated Account Architect',
      '99.99% Uptime Guarantee',
    ],
  },
]

const invoiceColumns = [
  { key: 'number', label: 'Invoice Number' },
  { key: 'date', label: 'Billing Date' },
  { key: 'amount', label: 'Amount' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: '', align: 'right' as const },
]

async function loadBilling() {
  isLoading.value = true
  try {
    const [sub, inv] = await Promise.all([
      billingApi.getSubscription(),
      billingApi.getInvoices(),
    ])
    subscription.value = sub
    invoices.value = inv
  } catch (err) {
    uiStore.error('Failed to load billing details')
  } finally {
    isLoading.value = false
  }
}

async function selectPlan(plan: typeof availablePlans[0]) {
  if (subscription.value?.plan === plan.id) return

  uiStore.confirm({
    title: `Switch to ${plan.name}?`,
    message: `Your workspace will be updated to ${plan.name} (${plan.price}${plan.period}). The prorated difference will be reflected on your next invoice.`,
    confirmText: 'Confirm Upgrade',
    cancelText: 'Cancel',
    type: 'info',
    onConfirm: async () => {
      isUpgrading.value = true
      try {
        const updated = await billingApi.updateSubscription(plan.id)
        subscription.value = updated
        uiStore.success(`Successfully switched to ${plan.name}!`)
      } catch (err: any) {
        uiStore.error('Could not update subscription')
      } finally {
        isUpgrading.value = false
      }
    },
  })
}

function downloadInvoice(invoice: InvoiceItem) {
  uiStore.info(`Downloading invoice ${invoice.number}...`)
}

onMounted(() => {
  loadBilling()
})
</script>

<template>
  <div class="space-y-6 text-left">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-black text-slate-900 tracking-tight">
        {{ t('billing.title') }}
      </h1>
      <p class="text-xs text-slate-500 mt-0.5">
        Manage your subscription tier, resource capacity, and tax invoice receipts.
      </p>
    </div>

    <!-- Current Subscription Status Card -->
    <AppCard v-if="subscription">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
          <div class="flex items-center gap-2 mb-2">
            <AppBadge variant="brand" size="md">
              Current Tier
            </AppBadge>
            <AppBadge :variant="subscription.status === 'active' ? 'success' : 'warning'" size="md" dot>
              {{ subscription.status.toUpperCase() }}
            </AppBadge>
          </div>
          <h3 class="text-xl font-bold text-slate-900">
            {{ subscription.plan_name }}
          </h3>
          <p class="text-xs text-slate-400 mt-1">
            Next renewal date on <strong class="text-slate-700 font-semibold">{{ subscription.renews_at }}</strong> &bull; Auto-renewal enabled
          </p>
        </div>

        <!-- Usage Meters -->
        <div class="grid grid-cols-2 gap-4 lg:w-80">
          <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Users</div>
            <div class="text-base font-extrabold text-slate-800 mt-0.5">
              {{ subscription.users_count }} / {{ subscription.users_limit }}
            </div>
            <div class="w-full bg-slate-200 h-1.5 rounded-full mt-2 overflow-hidden">
              <div
                class="bg-brand-600 h-full rounded-full"
                :style="{ width: `${(subscription.users_count / subscription.users_limit) * 100}%` }"
              />
            </div>
          </div>

          <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Branches</div>
            <div class="text-base font-extrabold text-slate-800 mt-0.5">
              {{ subscription.branches_count }} / {{ subscription.branches_limit }}
            </div>
            <div class="w-full bg-slate-200 h-1.5 rounded-full mt-2 overflow-hidden">
              <div
                class="bg-emerald-500 h-full rounded-full"
                :style="{ width: `${(subscription.branches_count / subscription.branches_limit) * 100}%` }"
              />
            </div>
          </div>
        </div>
      </div>
    </AppCard>

    <!-- Plan Selection Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        v-for="plan in availablePlans"
        :key="plan.id"
        :class="[
          'rounded-3xl p-6 border transition-all flex flex-col justify-between text-left relative',
          subscription?.plan === plan.id
            ? 'bg-white border-brand-500 ring-2 ring-brand-500 shadow-md'
            : plan.popular
            ? 'bg-white border-slate-200 shadow-sm'
            : 'bg-white border-slate-100 shadow-2xs',
        ]"
      >
        <div
          v-if="plan.popular"
          class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full bg-brand-600 text-white text-[10px] font-extrabold tracking-wider uppercase shadow-xs"
        >
          Most Popular
        </div>

        <div>
          <h4 class="text-base font-bold text-slate-900">{{ plan.name }}</h4>
          <p class="text-xs text-slate-400 mt-1 mb-4">{{ plan.description }}</p>

          <div class="flex items-baseline gap-1 my-4">
            <span class="text-2xl font-black text-slate-900">{{ plan.price }}</span>
            <span class="text-xs text-slate-400 font-medium">{{ plan.period }}</span>
          </div>

          <div class="space-y-2.5 my-6">
            <div
              v-for="feature in plan.features"
              :key="feature"
              class="flex items-center gap-2.5 text-xs text-slate-600"
            >
              <div class="w-4 h-4 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <Check class="w-2.5 h-2.5 stroke-[3]" />
              </div>
              <span>{{ feature }}</span>
            </div>
          </div>
        </div>

        <AppButton
          :variant="subscription?.plan === plan.id ? 'secondary' : 'primary'"
          size="md"
          :block="true"
          :disabled="subscription?.plan === plan.id || isUpgrading"
          @click="selectPlan(plan)"
        >
          <span v-if="subscription?.plan === plan.id">Active Plan</span>
          <span v-else>Select {{ plan.name }}</span>
        </AppButton>
      </div>
    </div>

    <!-- Invoices & Receipts History -->
    <AppCard
      title="Billing & Invoicing History"
      subtitle="Download official tax receipts and subscription invoices"
      no-padding
    >
      <AppTable
        :columns="invoiceColumns"
        :items="invoices"
        :loading="isLoading"
      >
        <template #cell-number="{ value }">
          <span class="font-bold text-slate-900 text-xs font-mono">{{ value }}</span>
        </template>

        <template #cell-status="{ value }">
          <AppBadge variant="success" size="sm" dot>
            {{ value.toUpperCase() }}
          </AppBadge>
        </template>

        <template #cell-amount="{ value }">
          <span class="font-semibold text-slate-800 text-xs">{{ value }}</span>
        </template>

        <template #cell-actions="{ item }">
          <button
            type="button"
            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-brand-600 rounded-lg hover:bg-slate-50 transition-colors"
            @click="downloadInvoice(item)"
          >
            <Download class="w-3.5 h-3.5" />
            <span>Download PDF</span>
          </button>
        </template>
      </AppTable>
    </AppCard>
  </div>
</template>
