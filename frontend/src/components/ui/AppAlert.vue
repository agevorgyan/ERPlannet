<script setup lang="ts">
import { computed } from 'vue'
import { Info, CheckCircle2, AlertTriangle, AlertCircle, X } from 'lucide-vue-next'

export interface AlertProps {
  type?: 'info' | 'success' | 'warning' | 'danger'
  title?: string
  closable?: boolean
}

const props = withDefaults(defineProps<AlertProps>(), {
  type: 'info',
  closable: false,
})

const emit = defineEmits<{
  (e: 'close'): void
}>()

const icon = computed(() => {
  switch (props.type) {
    case 'success':
      return CheckCircle2
    case 'warning':
      return AlertTriangle
    case 'danger':
      return AlertCircle
    case 'info':
    default:
      return Info
  }
})

const colorClasses = computed(() => {
  switch (props.type) {
    case 'success':
      return 'bg-emerald-50/80 border-emerald-200 text-emerald-900'
    case 'warning':
      return 'bg-amber-50/80 border-amber-200 text-amber-900'
    case 'danger':
      return 'bg-rose-50/80 border-rose-200 text-rose-900'
    case 'info':
    default:
      return 'bg-brand-50/80 border-brand-200 text-brand-900'
  }
})

const iconColor = computed(() => {
  switch (props.type) {
    case 'success':
      return 'text-emerald-600'
    case 'warning':
      return 'text-amber-600'
    case 'danger':
      return 'text-rose-600'
    case 'info':
    default:
      return 'text-brand-600'
  }
})
</script>

<template>
  <div :class="['rounded-2xl border p-4 flex items-start gap-3', colorClasses]">
    <component :is="icon" :class="['w-5 h-5 shrink-0 mt-0.5', iconColor]" />
    <div class="flex-1 text-left">
      <h5 v-if="title" class="text-xs font-bold mb-0.5">
        {{ title }}
      </h5>
      <div class="text-xs font-normal leading-relaxed">
        <slot />
      </div>
    </div>
    <button
      v-if="closable"
      type="button"
      class="text-slate-400 hover:text-slate-600 shrink-0 p-1"
      @click="emit('close')"
    >
      <X class="w-4 h-4" />
    </button>
  </div>
</template>
