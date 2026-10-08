<script setup lang="ts">
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Mail, ArrowLeft, CheckCircle2 } from 'lucide-vue-next'

import AuthLayout from '@/layouts/AuthLayout.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppAlert from '@/components/ui/AppAlert.vue'
import { authApi } from '@/api/auth'
import { useUiStore } from '@/stores/uiStore'

const { t } = useI18n()
const uiStore = useUiStore()

const email = ref('')
const isLoading = ref(false)
const errorMessage = ref('')
const isSuccess = ref(false)

async function handleForgotPassword() {
  if (!email.value) return
  isLoading.value = true
  errorMessage.value = ''
  try {
    await authApi.forgotPassword(email.value)
    isSuccess.value = true
    uiStore.success(t('auth.resetLinkSent'))
  } catch (err: any) {
    errorMessage.value = err.message || t('errors.serverError')
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <AuthLayout>
    <div class="text-left">
      <router-link
        to="/login"
        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 mb-4 transition-colors"
      >
        <ArrowLeft class="w-3.5 h-3.5" />
        <span>{{ t('auth.backToLogin') }}</span>
      </router-link>

      <h3 class="text-xl font-bold text-slate-900 tracking-tight">
        {{ t('auth.forgotPasswordTitle') }}
      </h3>
      <p class="text-xs text-slate-500 mt-1 mb-6">
        Enter your work email address and we will send you a password reset link.
      </p>

      <div v-if="isSuccess" class="p-6 rounded-2xl bg-emerald-50 border border-emerald-100 text-center">
        <CheckCircle2 class="w-10 h-10 text-emerald-600 mx-auto mb-2" />
        <h4 class="text-sm font-bold text-emerald-900">Check your email</h4>
        <p class="text-xs text-emerald-700 mt-1">
          We have sent password reset instructions to <strong class="font-semibold">{{ email }}</strong>.
        </p>
      </div>

      <form v-else class="space-y-4" @submit.prevent="handleForgotPassword">
        <AppAlert
          v-if="errorMessage"
          type="danger"
          class="mb-4"
          :closable="true"
          @close="errorMessage = ''"
        >
          {{ errorMessage }}
        </AppAlert>

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

        <div class="pt-2">
          <AppButton
            type="submit"
            variant="primary"
            size="md"
            :loading="isLoading"
            :block="true"
          >
            {{ t('auth.sendResetLink') }}
          </AppButton>
        </div>
      </form>
    </div>
  </AuthLayout>
</template>
