<script setup lang="ts">
import { ref, computed } from 'vue'
import { Eye, EyeOff } from 'lucide-vue-next'

export interface InputProps {
  modelValue?: string | number
  label?: string
  placeholder?: string
  type?: string
  id?: string
  name?: string
  error?: string
  hint?: string
  disabled?: boolean
  readonly?: boolean
  required?: boolean
}

const props = withDefaults(defineProps<InputProps>(), {
  modelValue: '',
  type: 'text',
  placeholder: '',
  disabled: false,
  readonly: false,
  required: false,
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | number): void
  (e: 'blur', event: FocusEvent): void
  (e: 'focus', event: FocusEvent): void
}>()

const inputId = computed(() => props.id || `input_${Math.random().toString(36).substring(2, 9)}`)
const showPassword = ref(false)

const computedType = computed(() => {
  if (props.type === 'password') {
    return showPassword.value ? 'text' : 'password'
  }
  return props.type
})

function handleInput(e: Event) {
  const target = e.target as HTMLInputElement
  emit('update:modelValue', target.value)
}
</script>

<template>
  <div class="w-full flex flex-col gap-1.5 text-left">
    <label
      v-if="label"
      :for="inputId"
      class="text-xs font-semibold text-slate-700 select-none flex items-center justify-between"
    >
      <span>
        {{ label }}
        <span v-if="required" class="text-rose-500 ml-0.5">*</span>
      </span>
      <slot name="label-right" />
    </label>

    <div class="relative flex items-center">
      <div v-if="$slots.leading" class="absolute left-3.5 flex items-center pointer-events-none text-slate-400">
        <slot name="leading" />
      </div>

      <input
        :id="inputId"
        :name="name"
        :type="computedType"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :class="[
          'w-full text-sm text-slate-900 bg-white placeholder:text-slate-400 rounded-xl transition-all duration-150',
          'border outline-none focus:ring-4',
          error
            ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/10 bg-rose-50/20'
            : 'border-slate-200 hover:border-slate-300 focus:border-brand-500 focus:ring-brand-500/10',
          $slots.leading ? 'pl-10' : 'pl-3.5',
          $slots.trailing || type === 'password' ? 'pr-10' : 'pr-3.5',
          'py-2.5',
          disabled ? 'bg-slate-50 text-slate-400 cursor-not-allowed select-none' : '',
        ]"
        @input="handleInput"
        @blur="emit('blur', $event)"
        @focus="emit('focus', $event)"
      />

      <div v-if="type === 'password'" class="absolute right-3.5 flex items-center">
        <button
          type="button"
          class="text-slate-400 hover:text-slate-600 focus:outline-none"
          tabindex="-1"
          @click="showPassword = !showPassword"
        >
          <EyeOff v-if="showPassword" class="w-4 h-4" />
          <Eye v-else class="w-4 h-4" />
        </button>
      </div>

      <div v-else-if="$slots.trailing" class="absolute right-3.5 flex items-center pointer-events-none text-slate-400">
        <slot name="trailing" />
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
