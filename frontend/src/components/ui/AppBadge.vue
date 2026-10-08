<script setup lang="ts">
import { computed } from 'vue'

export interface BadgeProps {
  variant?: 'brand' | 'success' | 'warning' | 'danger' | 'neutral' | 'purple' | 'cyan'
  size?: 'sm' | 'md'
  dot?: boolean
}

const props = withDefaults(defineProps<BadgeProps>(), {
  variant: 'neutral',
  size: 'sm',
  dot: false,
})

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'brand':
      return 'bg-brand-50 text-brand-700 border-brand-200/60'
    case 'success':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200/60'
    case 'warning':
      return 'bg-amber-50 text-amber-700 border-amber-200/60'
    case 'danger':
      return 'bg-rose-50 text-rose-700 border-rose-200/60'
    case 'purple':
      return 'bg-purple-50 text-purple-700 border-purple-200/60'
    case 'cyan':
      return 'bg-cyan-50 text-cyan-700 border-cyan-200/60'
    case 'neutral':
    default:
      return 'bg-slate-100 text-slate-700 border-slate-200/60'
  }
})

const dotColor = computed(() => {
  switch (props.variant) {
    case 'brand':
      return 'bg-brand-500'
    case 'success':
      return 'bg-emerald-500'
    case 'warning':
      return 'bg-amber-500'
    case 'danger':
      return 'bg-rose-500'
    case 'purple':
      return 'bg-purple-500'
    case 'cyan':
      return 'bg-cyan-500'
    case 'neutral':
    default:
      return 'bg-slate-400'
  }
})

const sizeClasses = computed(() => {
  return props.size === 'sm'
    ? 'text-xs px-2.5 py-0.5 gap-1.5 font-medium'
    : 'text-xs px-3 py-1 gap-2 font-semibold'
})
</script>

<template>
  <span
    :class="[
      'inline-flex items-center rounded-full border tracking-wide select-none',
      variantClasses,
      sizeClasses,
    ]"
  >
    <span v-if="dot" :class="['w-1.5 h-1.5 rounded-full shrink-0', dotColor]" />
    <slot />
  </span>
</template>
