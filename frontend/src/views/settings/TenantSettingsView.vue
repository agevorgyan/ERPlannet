<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { Building2, Globe, ShieldAlert, Save, RefreshCw } from 'lucide-vue-next'

import AppCard from '@/components/ui/AppCard.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppAlert from '@/components/ui/AppAlert.vue'
import { useTenantStore } from '@/stores/tenantStore'
import { useUiStore } from '@/stores/uiStore'

const { t } = useI18n()
const tenantStore = useTenantStore()
const uiStore = useUiStore()

const isLoading = ref(false)
const isSaving = ref(false)

const form = ref({
  name: '',
  slug: '',
  domain: '',
  tax_number: '02548963',
  email: 'operations@gevorgyan-foods.am',
  phone: '+374 10 55-44-33',
  address: 'Yerevan, Sayat-Nova Ave 12',
  currency: 'AMD',
  timezone: 'Asia/Yerevan',
  locale: 'hy',
})

const currencyOptions = [
  { label: 'AMD — Armenian Dram (֏)', value: 'AMD' },
  { label: 'USD — US Dollar ($)', value: 'USD' },
  { label: 'EUR — Euro (€)', value: 'EUR' },
  { label: 'RUB — Russian Ruble (₽)', value: 'RUB' },
]

const timezoneOptions = [
  { label: 'Asia/Yerevan (GMT+4)', value: 'Asia/Yerevan' },
  { label: 'Europe/Moscow (GMT+3)', value: 'Europe/Moscow' },
  { label: 'UTC (GMT+0)', value: 'UTC' },
]

const localeOptions = [
  { label: '🇦🇲 Հայերեն (Armenian)', value: 'hy' },
  { label: '🇺🇸 English', value: 'en' },
  { label: '🇷🇺 Русский (Russian)', value: 'ru' },
]

onMounted(async () => {
  isLoading.value = true
  try {
    const data = await tenantStore.fetchTenantInfo()
    if (data) {
      form.value.name = data.name
      form.value.slug = data.slug
      form.value.domain = data.domain || `${data.slug}.erplannet.am`
      form.value.currency = data.currency || 'AMD'
      form.value.locale = data.locale || 'hy'
    }
  } finally {
    isLoading.value = false
  }
})

async function saveSettings() {
  isSaving.value = true
  try {
    await tenantStore.updateSettings({
      name: form.value.name,
      currency: form.value.currency,
      timezone: form.value.timezone,
      locale: form.value.locale,
    })
    uiStore.success(t('messages.savedSuccess'), 'Organization Settings')
  } catch (err: any) {
    uiStore.error(err.message || 'Failed to save settings')
  } finally {
    isSaving.value = false
  }
}
</script>

<template>
  <div class="max-w-4xl space-y-6 text-left">
    <div>
      <h1 class="text-2xl font-black text-slate-900 tracking-tight">
        {{ t('settings.tenantProfile') }}
      </h1>
      <p class="text-xs text-slate-500 mt-0.5">
        Manage your multi-tenant organization identifiers, domain routing, and regional preferences.
      </p>
    </div>

    <form class="space-y-6" @submit.prevent="saveSettings">
      <!-- General Organization Details -->
      <AppCard
        title="Organization Details"
        subtitle="Basic company information and fiscal numbers"
      >
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <AppInput
            v-model="form.name"
            label="Organization Legal Name"
            placeholder="e.g. Gevorgyan Bakery"
            required
          />

          <AppInput
            v-model="form.tax_number"
            label="Tax Identification (HVHH / ՀՎՀՀ)"
            placeholder="8-digit tax code"
          />

          <AppInput
            v-model="form.email"
            type="email"
            label="Official Contact Email"
            placeholder="contact@company.am"
          />

          <AppInput
            v-model="form.phone"
            label="Phone Number"
            placeholder="+374 10 00-00-00"
          />

          <div class="sm:col-span-2">
            <AppInput
              v-model="form.address"
              label="Registered Business Address"
              placeholder="City, Street, Building"
            />
          </div>
        </div>
      </AppCard>

      <!-- Multi-Tenant Domains & Custom Subdomains -->
      <AppCard
        title="Tenant Routing & Domains"
        subtitle="Subdomain routing and custom enterprise domain mapping"
      >
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <AppInput
            v-model="form.slug"
            label="Platform Subdomain Slug"
            placeholder="workspace-slug"
            disabled
            hint="System slug used for header identification (immutable)"
          />

          <AppInput
            v-model="form.domain"
            label="Custom Domain Mapping"
            placeholder="erp.mycompany.am"
            hint="CNAME to erplannet.am with automated SSL certification"
          />
        </div>
      </AppCard>

      <!-- Regional & Financial Preferences -->
      <AppCard
        title="Regional & Currency Settings"
        subtitle="Base accounting currency and timezone"
      >
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <AppSelect
            v-model="form.currency"
            :options="currencyOptions"
            label="Base Currency"
          />

          <AppSelect
            v-model="form.timezone"
            :options="timezoneOptions"
            label="Default Timezone"
          />

          <AppSelect
            v-model="form.locale"
            :options="localeOptions"
            label="Default Language"
          />
        </div>
      </AppCard>

      <!-- Submit Bar -->
      <div class="flex items-center justify-end gap-3 pt-2">
        <AppButton
          type="submit"
          variant="primary"
          size="md"
          :loading="isSaving"
        >
          <template #leading>
            <Save class="w-4 h-4" />
          </template>
          <span>Save Changes</span>
        </AppButton>
      </div>
    </form>
  </div>
</template>
