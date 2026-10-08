<script setup lang="ts">
import { computed } from 'vue'
import { Loader2 } from 'lucide-vue-next'

export interface ButtonProps {
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger' | 'success'
  size?: 'xs' | 'sm' | 'md' | 'lg'
  type?: 'button' | 'submit' | 'reset'
  disabled?: boolean
  loading?: boolean
  block?: boolean
}

const props = withDefaults(defineProps<ButtonProps>(), {
  variant: 'primary',
  size: 'md',
  type: 'button',
  disabled: false,
  loading: false,
  block: false,
})

const emit = defineEmits<{
  (e: 'click', event: MouseEvent): void
}>()

const baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none disabled:active:scale-100 rounded-xl'

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'primary':
      return 'bg-brand-600 hover:bg-brand-700 text-white shadow-sm shadow-brand-500/25 focus:ring-brand-500 border border-transparent'
    case 'secondary':
      return 'bg-slate-100 hover:bg-slate-200 text-slate-800 focus:ring-slate-400 border border-transparent'
    case 'outline':
      return 'border border-slate-300 hover:border-slate-400 bg-white hover:bg-slate-50 text-slate-700 focus:ring-brand-500 shadow-xs'
    case 'ghost':
      return 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus:ring-slate-300'
    case 'danger':
      return 'bg-rose-600 hover:bg-rose-700 text-white shadow-sm shadow-rose-500/25 focus:ring-rose-500 border border-transparent'
    case 'success':
      return 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm shadow-emerald-500/25 focus:ring-emerald-500 border border-transparent'
    default:
      return 'bg-brand-600 text-white hover:bg-brand-700'
  }
})

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'xs':
      return 'text-xs px-2.5 py-1.5 gap-1.5'
    case 'sm':
      return 'text-xs font-semibold px-3 py-2 gap-1.5'
    case 'md':
      return 'text-sm px-4 py-2.5 gap-2'
    case 'lg':
      return 'text-base px-5 py-3 gap-2.5'
    default:
      return 'text-sm px-4 py-2.5 gap-2'
  }
})

function handleClick(e: MouseEvent) {
  if (!props.disabled && !props.loading) {
    emit('click', e)
  }
}
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      baseClasses,
      variantClasses,
      sizeClasses,
      block ? 'w-full' : '',
    ]"
    @click="handleClick"
  >
    <Loader2 v-if="loading" class="w-4 h-4 animate-spin shrink-0" />
    <slot name="leading" />
    <slot />
    <slot name="trailing" />
  </button>
</template>
