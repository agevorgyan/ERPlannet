<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { Mail, Lock, Building2 } from 'lucide-vue-next'

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

const tenantSlug = ref(tenantStore.tenantSlug || 'gevorgyan-foods')
const email = ref('admin@erplannet.am')
const password = ref('password')
const rememberMe = ref(true)
const errorMessage = ref('')
const isLoading = ref(false)

async function handleLogin() {
  errorMessage.value = ''
  if (!email.value || !password.value) {
    errorMessage.value = 'Please provide both email and password.'
    return
  }

  isLoading.value = true
  try {
    if (tenantSlug.value) {
      tenantStore.setTenantSlug(tenantSlug.value)
    }

    await authStore.login({
      email: email.value,
      password: password.value,
      tenant_slug: tenantSlug.value,
    })

    // Fetch tenant details
    await tenantStore.fetchTenantInfo(tenantSlug.value)

    uiStore.success(t('auth.loginSuccess'))
    router.push('/dashboard')
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
        {{ t('auth.loginTitle') }}
      </h3>
      <p class="text-xs text-slate-500 mt-1 mb-6">
        Sign in to your multi-tenant ERP organization workspace
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

      <form class="space-y-4" @submit.prevent="handleLogin">
        <!-- Tenant Workspace Slug -->
        <AppInput
          v-model="tenantSlug"
          :label="t('auth.tenantSlug')"
          placeholder="your-company"
          hint="Subdomain or organization identifier (e.g. acme)"
          required
        >
          <template #leading>
            <Building2 class="w-4 h-4" />
          </template>
        </AppInput>

        <!-- Email -->
        <AppInput
          v-model="email"
          type="email"
          :label="t('auth.email')"
          placeholder="name@company.com"
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
          placeholder="••••••••"
          required
        >
          <template #leading>
            <Lock class="w-4 h-4" />
          </template>
          <template #label-right>
            <router-link
              to="/forgot-password"
              class="text-xs font-semibold text-brand-600 hover:text-brand-700"
            >
              {{ t('auth.forgotPassword') }}
            </router-link>
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
            {{ t('auth.signIn') }}
          </AppButton>
        </div>
      </form>

      <!-- Bottom Register Link -->
      <div class="mt-6 text-center text-xs text-slate-500">
        {{ t('auth.dontHaveAccount') }}
        <router-link
          to="/register"
          class="font-semibold text-brand-600 hover:text-brand-700 ml-1"
        >
          {{ t('auth.signUp') }}
        </router-link>
      </div>
    </div>
  </AuthLayout>
</template>
