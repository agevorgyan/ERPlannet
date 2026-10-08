<script setup lang="ts">
import { computed } from 'vue'
import { Check } from 'lucide-vue-next'

export interface CheckboxProps {
  modelValue?: boolean
  label?: string
  description?: string
  disabled?: boolean
  id?: string
}

const props = withDefaults(defineProps<CheckboxProps>(), {
  modelValue: false,
  disabled: false,
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
}>()

const inputId = computed(() => props.id || `chk_${Math.random().toString(36).substring(2, 9)}`)

function toggle() {
  if (!props.disabled) {
    emit('update:modelValue', !props.modelValue)
  }
}
</script>

<template>
  <label
    :for="inputId"
    :class="[
      'inline-flex items-start gap-3 select-none cursor-pointer group',
      disabled ? 'cursor-not-allowed opacity-60' : '',
    ]"
  >
    <div class="relative flex items-center justify-center mt-0.5">
      <input
        :id="inputId"
        type="checkbox"
        :checked="modelValue"
        :disabled="disabled"
        class="sr-only"
        @change="toggle"
      />
      <div
        :class="[
          'w-5 h-5 rounded-md border flex items-center justify-center transition-all duration-150',
          modelValue
            ? 'bg-brand-600 border-brand-600 text-white shadow-xs'
            : 'border-slate-300 bg-white group-hover:border-slate-400',
        ]"
      >
        <Check v-if="modelValue" class="w-3.5 h-3.5 stroke-[3]" />
      </div>
    </div>
    <div v-if="label || description" class="flex flex-col text-left">
      <span v-if="label" class="text-sm font-medium text-slate-700 leading-snug">
        {{ label }}
      </span>
      <span v-if="description" class="text-xs text-slate-400">
        {{ description }}
      </span>
    </div>
  </label>
</template>
