<script setup lang="ts">
import { computed } from 'vue'
import { CheckCircle2, AlertCircle, AlertTriangle, Info, X } from 'lucide-vue-next'
import { useUiStore, type ToastItem } from '@/stores/uiStore'

const uiStore = useUiStore()
const toasts = computed(() => uiStore.toasts)

function getToastIcon(type: ToastItem['type']) {
  switch (type) {
    case 'success':
      return CheckCircle2
    case 'error':
      return AlertCircle
    case 'warning':
      return AlertTriangle
    case 'info':
    default:
      return Info
  }
}

function getToastStyles(type: ToastItem['type']) {
  switch (type) {
    case 'success':
      return {
        border: 'border-emerald-200 bg-white text-emerald-900',
        icon: 'text-emerald-500 bg-emerald-50',
      }
    case 'error':
      return {
        border: 'border-rose-200 bg-white text-rose-900',
        icon: 'text-rose-500 bg-rose-50',
      }
    case 'warning':
      return {
        border: 'border-amber-200 bg-white text-amber-900',
        icon: 'text-amber-500 bg-amber-50',
      }
    case 'info':
    default:
      return {
        border: 'border-brand-200 bg-white text-brand-900',
        icon: 'text-brand-500 bg-brand-50',
      }
  }
}
</script>

<template>
  <Teleport to="body">
    <div
      class="fixed bottom-5 right-5 z-50 flex flex-col gap-3 max-w-sm w-full pointer-events-none"
    >
      <TransitionGroup
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform translate-y-4 opacity-0 scale-95"
        enter-to-class="transform translate-y-0 opacity-100 scale-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="transform translate-y-0 opacity-100 scale-100"
        leave-to-class="transform translate-y-4 opacity-0 scale-95"
      >
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="[
            'pointer-events-auto flex items-start gap-3 p-4 rounded-2xl border shadow-lg shadow-slate-900/5',
            getToastStyles(toast.type).border,
          ]"
        >
          <div :class="['w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5', getToastStyles(toast.type).icon]">
            <component :is="getToastIcon(toast.type)" class="w-4 h-4" />
          </div>

          <div class="flex-1 text-left min-w-0">
            <h4 v-if="toast.title" class="text-xs font-bold text-slate-800">
              {{ toast.title }}
            </h4>
            <p class="text-xs text-slate-600 font-medium leading-relaxed mt-0.5">
              {{ toast.message }}
            </p>
          </div>

          <button
            type="button"
            class="text-slate-400 hover:text-slate-600 rounded-md p-1 hover:bg-slate-100 shrink-0"
            @click="uiStore.removeToast(toast.id)"
          >
            <X class="w-3.5 h-3.5" />
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>
