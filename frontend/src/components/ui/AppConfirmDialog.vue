<script setup lang="ts">
import { computed } from 'vue'
import { AlertTriangle, Info, AlertCircle } from 'lucide-vue-next'
import { useUiStore } from '@/stores/uiStore'
import AppButton from './AppButton.vue'

const uiStore = computed(() => useUiStore())
const options = computed(() => uiStore.value.confirmDialogOptions)

const iconComponent = computed(() => {
  switch (options.value?.type) {
    case 'danger':
      return AlertCircle
    case 'warning':
      return AlertTriangle
    case 'info':
    default:
      return Info
  }
})

const iconBg = computed(() => {
  switch (options.value?.type) {
    case 'danger':
      return 'bg-rose-100 text-rose-600'
    case 'warning':
      return 'bg-amber-100 text-amber-600'
    case 'info':
    default:
      return 'bg-brand-100 text-brand-600'
  }
})

const confirmVariant = computed(() => {
  return options.value?.type === 'danger' ? 'danger' : 'primary'
})
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="uiStore.isConfirmDialogOpen && options"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
      >
        <div class="relative bg-white rounded-2xl shadow-xl border border-slate-100 max-w-md w-full p-6 text-left">
          <div class="flex items-start gap-4">
            <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0', iconBg]">
              <component :is="iconComponent" class="w-5 h-5" />
            </div>
            <div class="flex-1">
              <h3 class="text-base font-bold text-slate-900">
                {{ options.title }}
              </h3>
              <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                {{ options.message }}
              </p>
            </div>
          </div>

          <div class="mt-6 flex items-center justify-end gap-3">
            <AppButton
              variant="outline"
              size="md"
              :disabled="uiStore.isConfirmLoading"
              @click="uiStore.handleCancel"
            >
              {{ options.cancelText || 'Cancel' }}
            </AppButton>
            <AppButton
              :variant="confirmVariant"
              size="md"
              :loading="uiStore.isConfirmLoading"
              @click="uiStore.handleConfirm"
            >
              {{ options.confirmText || 'Confirm' }}
            </AppButton>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
