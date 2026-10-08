<script setup lang="ts">
import { computed } from 'vue'
import { ChevronDown } from 'lucide-vue-next'

export interface SelectOption {
  label: string
  value: any
  disabled?: boolean
}

export interface SelectProps {
  modelValue?: any
  options?: SelectOption[]
  label?: string
  placeholder?: string
  id?: string
  error?: string
  hint?: string
  disabled?: boolean
  required?: boolean
}

const props = withDefaults(defineProps<SelectProps>(), {
  modelValue: '',
  options: () => [],
  placeholder: '',
  disabled: false,
  required: false,
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: any): void
  (e: 'change', event: Event): void
}>()

const selectId = computed(() => props.id || `select_${Math.random().toString(36).substring(2, 9)}`)

function handleChange(e: Event) {
  const target = e.target as HTMLSelectElement
  emit('update:modelValue', target.value)
  emit('change', e)
}
</script>

<template>
  <div class="w-full flex flex-col gap-1.5 text-left">
    <label
      v-if="label"
      :for="selectId"
      class="text-xs font-semibold text-slate-700 select-none flex items-center justify-between"
    >
      <span>
        {{ label }}
        <span v-if="required" class="text-rose-500 ml-0.5">*</span>
      </span>
      <slot name="label-right" />
    </label>

    <div class="relative flex items-center">
      <select
        :id="selectId"
        :value="modelValue"
        :disabled="disabled"
        :class="[
          'w-full text-sm text-slate-900 bg-white rounded-xl appearance-none transition-all duration-150',
          'border outline-none focus:ring-4 pl-3.5 pr-10 py-2.5',
          error
            ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/10 bg-rose-50/20'
            : 'border-slate-200 hover:border-slate-300 focus:border-brand-500 focus:ring-brand-500/10',
          disabled ? 'bg-slate-50 text-slate-400 cursor-not-allowed select-none' : '',
        ]"
        @change="handleChange"
      >
        <option v-if="placeholder" value="" disabled :selected="!modelValue">
          {{ placeholder }}
        </option>
        <option
          v-for="opt in options"
          :key="opt.value"
          :value="opt.value"
          :disabled="opt.disabled"
        >
          {{ opt.label }}
        </option>
        <slot />
      </select>

      <div class="absolute right-3.5 flex items-center pointer-events-none text-slate-400">
        <ChevronDown class="w-4 h-4" />
      </div>
    </div>

    <p v-if="error" class="text-xs text-rose-600 font-medium">
      {{ error }}
    </p>
    <p v-else-if="hint" class="text-xs text-slate-400">
      {{ hint }}
    </p>
  </div>
</template>
