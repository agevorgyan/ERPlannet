<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { Mail, Lock, User, Building, Globe } from 'lucide-vue-next'

import AuthLayout from '@/layouts/AuthLayout.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppAlert from '@/components/ui/AppAlert.vue'
import { useAuthStore } from '@/stores/authStore'
import { useTenantStore } from '@/stores/tenantStore'
import { useUiStore } from '@/stores/uiStore'

const router = useRouter()
const { t } = useI18n()
const authStore = useAuthStore()
const tenantStore = useTenantStore()
const uiStore = useUiStore()

const name = ref('')
const email = ref('')
const companyName = ref('')
const tenantSlug = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const errorMessage = ref('')
const isLoading = ref(false)

// Auto slugify company name if tenantSlug is not manually typed
function handleCompanyInput(val: string | number) {
  companyName.value = String(val)
  if (!tenantSlug.value || tenantSlug.value === slugify(companyName.value.slice(0, -1))) {
    tenantSlug.value = slugify(String(val))
  }
}

function slugify(text: string): string {
  return text
    .toString()
    .toLowerCase()
    .trim()
    .replace(/\s+/g, '-')
    .replace(/[^\w\-]+/g, '')
    .replace(/\-\-+/g, '-')
}

async function handleRegister() {
  errorMessage.value = ''
  if (!name.value || !email.value || !password.value || !companyName.value) {
    errorMessage.value = 'Please complete all required fields.'
    return
  }

  if (password.value !== passwordConfirmation.value) {
    errorMessage.value = 'Passwords do not match.'
    return
  }

  isLoading.value = true
  try {
    const slug = tenantSlug.value || slugify(companyName.value)
    tenantStore.setTenantSlug(slug)

    await authStore.register({
      name: name.value,
      email: email.value,
      company_name: companyName.value,
      tenant_slug: slug,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })

    uiStore.success(t('auth.registerSuccess'))
    router.push('/onboarding')
  } catch (err: any) {
    errorMessage.value = err.message || t('errors.serverError')
    uiStore.error(errorMessage.value)
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <AuthLayout>
    <div class="text-left">
      <h3 class="text-xl font-bold text-slate-900 tracking-tight">
        {{ t('auth.registerTitle') }}
      </h3>
      <p class="text-xs text-slate-500 mt-1 mb-6">
        Create your organization workspace on ERPlannet
      </p>

      <AppAlert
        v-if="errorMessage"
        type="danger"
        class="mb-5"
        :closable="true"
        @close="errorMessage = ''"
      >
        {{ errorMessage }}
      </AppAlert>

      <form class="space-y-4" @submit.prevent="handleRegister">
        <!-- Company Name -->
        <AppInput
          :model-value="companyName"
          :label="t('auth.companyName')"
          placeholder="e.g. Ararat Bakery Ltd"
          required
          @update:model-value="handleCompanyInput"
        >
          <template #leading>
            <Building class="w-4 h-4" />
          </template>
        </AppInput>

        <!-- Subdomain slug -->
        <AppInput
          v-model="tenantSlug"
          :label="t('auth.tenantSlug')"
          placeholder="ararat-bakery"
          :hint="`Workspace URL: ${tenantSlug || 'your-company'}.erplannet.am`"
          required
        >
          <template #leading>
            <Globe class="w-4 h-4" />
          </template>
        </AppInput>

        <!-- Full Name -->
        <AppInput
          v-model="name"
          :label="t('auth.name')"
          placeholder="Aram Petrosyan"
          required
        >
          <template #leading>
            <User class="w-4 h-4" />
          </template>
        </AppInput>

        <!-- Work Email -->
        <AppInput
          v-model="email"
          type="email"
          :label="t('auth.email')"
          placeholder="aram@araratbakery.am"
          required
        >
          <template #leading>
            <Mail class="w-4 h-4" />
          </template>
        </AppInput>

        <!-- Password -->
        <AppInput
          v-model="password"
          type="password"
          :label="t('auth.password')"
          placeholder="Minimum 8 characters"
          required
        >
          <template #leading>
            <Lock class="w-4 h-4" />
          </template>
        </AppInput>

        <!-- Password Confirmation -->
        <AppInput
          v-model="passwordConfirmation"
          type="password"
          :label="t('auth.passwordConfirmation')"
          placeholder="Repeat password"
          required
        >
          <template #leading>
            <Lock class="w-4 h-4" />
          </template>
        </AppInput>

        <!-- Submit Button -->
        <div class="pt-2">
          <AppButton
            type="submit"
            variant="primary"
            size="md"
            :loading="isLoading"
            :block="true"
          >
            {{ t('auth.createWorkspace') }}
          </AppButton>
        </div>
      </form>

      <!-- Bottom Login Link -->
      <div class="mt-6 text-center text-xs text-slate-500">
        {{ t('auth.alreadyHaveAccount') }}
        <router-link
          to="/login"
          class="font-semibold text-brand-600 hover:text-brand-700 ml-1"
        >
          {{ t('auth.signIn') }}
        </router-link>
      </div>
    </div>
  </AuthLayout>
</template>
