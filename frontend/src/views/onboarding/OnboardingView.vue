<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import {
  Building2,
  Globe2,
  Users2,
  CheckCircle,
  ArrowRight,
  ArrowLeft,
  Sparkles,
  Layers,
} from 'lucide-vue-next'

import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { useTenantStore } from '@/stores/tenantStore'
import { useUiStore } from '@/stores/uiStore'

const router = useRouter()
const { t } = useI18n()
const tenantStore = useTenantStore()
const uiStore = useUiStore()

const currentStep = ref(1)
const isSubmitting = ref(false)

// Form fields
const companyName = ref(tenantStore.tenantName || 'Ararat Agro Foods')
const taxNumber = ref('02548963')
const industry = ref('manufacturing')
const currency = ref('AMD')
const timezone = ref('Asia/Yerevan')
const inviteEmail = ref('manager@company.am')
const inviteRole = ref('Manager')

const industryOptions = [
  { label: 'Food Production & Manufacturing', value: 'manufacturing' },
  { label: 'Retail & Supermarket Chain', value: 'retail' },
  { label: 'Distribution & Logistics', value: 'logistics' },
  { label: 'Restaurant & Hospitality / F&B', value: 'hospitality' },
  { label: 'Services & Consulting', value: 'services' },
]

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

async function nextStep() {
  if (currentStep.value < 3) {
    currentStep.value++
  } else {
    // Complete Onboarding
    isSubmitting.value = true
    try {
      await tenantStore.updateSettings({
        name: companyName.value,
        currency: currency.value,
        timezone: timezone.value,
      })
      uiStore.success('Workspace configured successfully! Welcome to ERPlannet.')
      router.push('/dashboard')
    } catch {
      uiStore.error('Could not save workspace preferences')
    } finally {
      isSubmitting.value = false
    }
  }
}

function prevStep() {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-2xl text-center mb-8">
      <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-brand-600 text-white shadow-lg shadow-brand-500/25 mb-4">
        <Layers class="w-6 h-6 stroke-[2.2]" />
      </div>
      <h2 class="text-2xl font-black text-slate-900 tracking-tight">
        {{ t('onboarding.welcome') }}
      </h2>
      <p class="text-xs text-slate-500 mt-1">
        Configure your multi-tenant ERP foundation in 3 quick steps
      </p>

      <!-- Stepper Indicator -->
      <div class="mt-8 flex items-center justify-center gap-2">
        <div
          v-for="step in 3"
          :key="step"
          class="flex items-center"
        >
          <div
            :class="[
              'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all',
              step === currentStep
                ? 'bg-brand-600 text-white ring-4 ring-brand-100'
                : step < currentStep
                ? 'bg-emerald-500 text-white'
                : 'bg-slate-200 text-slate-500',
            ]"
          >
            <CheckCircle v-if="step < currentStep" class="w-4 h-4 stroke-[3]" />
            <span v-else>{{ step }}</span>
          </div>
          <div
            v-if="step < 3"
            :class="[
              'w-12 h-0.5 mx-2',
              step < currentStep ? 'bg-emerald-500' : 'bg-slate-200',
            ]"
          />
        </div>
      </div>
    </div>

    <!-- Stepper Card -->
    <div class="sm:mx-auto sm:w-full sm:max-w-2xl">
      <div class="bg-white py-8 px-6 sm:px-10 shadow-xl shadow-slate-900/5 rounded-3xl border border-slate-100 text-left">
        <!-- Step 1: Organization Details -->
        <div v-if="currentStep === 1" class="space-y-4">
          <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
            <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
              <Building2 class="w-5 h-5" />
            </div>
            <div>
              <h4 class="text-base font-bold text-slate-900">Organization Profile</h4>
              <p class="text-xs text-slate-400">Company name, industry, and fiscal identification</p>
            </div>
          </div>

          <AppInput
            v-model="companyName"
            label="Organization Legal Name"
            placeholder="e.g. Ararat Agro Foods CJSC"
            required
          />

          <AppInput
            v-model="taxNumber"
            label="Tax Identification Number (HVHH / ՀՎՀՀ)"
            placeholder="e.g. 02548963"
            hint="Armenian 8-digit tax code for fiscal invoices"
          />

          <AppSelect
            v-model="industry"
            :options="industryOptions"
            label="Primary Business Industry"
          />
        </div>

        <!-- Step 2: Currency & Localization -->
        <div v-else-if="currentStep === 2" class="space-y-4">
          <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
              <Globe2 class="w-5 h-5" />
            </div>
            <div>
              <h4 class="text-base font-bold text-slate-900">Regional & Financial Settings</h4>
              <p class="text-xs text-slate-400">Default reporting currency and fiscal timezone</p>
            </div>
          </div>

          <AppSelect
            v-model="currency"
            :options="currencyOptions"
            label="Base Currency"
            hint="Accounting records and invoices will default to this currency"
          />

          <AppSelect
            v-model="timezone"
            :options="timezoneOptions"
            label="System Timezone"
          />
        </div>

        <!-- Step 3: Invite Team Members -->
        <div v-else class="space-y-4">
          <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <Users2 class="w-5 h-5" />
            </div>
            <div>
              <h4 class="text-base font-bold text-slate-900">Invite Colleagues</h4>
              <p class="text-xs text-slate-400">Bring your department managers into the workspace</p>
            </div>
          </div>

          <AppInput
            v-model="inviteEmail"
            type="email"
            label="Colleague Email Address"
            placeholder="colleague@company.am"
          />

          <AppInput
            v-model="inviteRole"
            label="Initial Role"
            placeholder="Manager / Accountant"
          />

          <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-500">
            You can manage detailed role permissions, branch assignments, and access policies in Settings &gt; Roles anytime.
          </div>
        </div>

        <!-- Step Actions -->
        <div class="mt-8 pt-5 border-t border-slate-100 flex items-center justify-between">
          <button
            v-if="currentStep > 1"
            type="button"
            class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-slate-900"
            @click="prevStep"
          >
            <ArrowLeft class="w-4 h-4" />
            <span>Back</span>
          </button>
          <div v-else />

          <AppButton
            variant="primary"
            size="md"
            :loading="isSubmitting"
            @click="nextStep"
          >
            <span>{{ currentStep === 3 ? 'Launch Dashboard' : 'Continue' }}</span>
            <ArrowRight v-if="currentStep < 3" class="w-4 h-4 ml-1" />
            <Sparkles v-else class="w-4 h-4 ml-1" />
          </AppButton>
        </div>
      </div>
    </div>
  </div>
</template>
