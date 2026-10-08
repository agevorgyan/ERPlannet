<script setup lang="ts">
import { ref } from 'vue'
import { Search, X } from 'lucide-vue-next'

export interface SearchProps {
  modelValue?: string
  placeholder?: string
  shortcut?: string
}

const props = withDefaults(defineProps<SearchProps>(), {
  modelValue: '',
  placeholder: 'Search...',
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'search', value: string): void
}>()

const inputRef = ref<HTMLInputElement | null>(null)

function handleInput(e: Event) {
  const target = e.target as HTMLInputElement
  emit('update:modelValue', target.value)
  emit('search', target.value)
}

function clear() {
  emit('update:modelValue', '')
  emit('search', '')
  inputRef.value?.focus()
}
</script>

<template>
  <div class="relative flex items-center w-full">
    <div class="absolute left-3.5 flex items-center pointer-events-none text-slate-400">
      <Search class="w-4 h-4" />
    </div>

    <input
      ref="inputRef"
      type="text"
      :value="modelValue"
      :placeholder="placeholder"
      class="w-full text-sm text-slate-900 bg-slate-50 hover:bg-slate-100/70 focus:bg-white rounded-xl border border-slate-200/80 focus:border-brand-500 pl-10 pr-12 py-2 transition-all outline-none focus:ring-4 focus:ring-brand-500/10 placeholder:text-slate-400"
      @input="handleInput"
    />

    <div class="absolute right-3 flex items-center gap-1.5">
      <button
        v-if="modelValue"
        type="button"
        class="text-slate-400 hover:text-slate-600 p-0.5 rounded-full"
        @click="clear"
      >
        <X class="w-3.5 h-3.5" />
      </button>

      <kbd
        v-else-if="shortcut"
        class="hidden sm:inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[10px] font-semibold text-slate-400 bg-white border border-slate-200 rounded-md shadow-2xs pointer-events-none"
      >
        {{ shortcut }}
      </kbd>
    </div>
  </div>
</template>
