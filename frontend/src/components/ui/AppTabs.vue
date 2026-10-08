<script setup lang="ts">
export interface TabItem {
  key: string
  label: string
  icon?: any
  count?: number | string
}

export interface TabsProps {
  modelValue: string
  tabs: TabItem[]
  variant?: 'pill' | 'underline'
}

withDefaults(defineProps<TabsProps>(), {
  variant: 'pill',
})

const emit = defineEmits<{
  (e: 'update:modelValue', key: string): void
}>()
</script>

<template>
  <div class="flex items-center gap-1.5 p-1 bg-slate-100/80 rounded-xl w-fit">
    <button
      v-for="tab in tabs"
      :key="tab.key"
      type="button"
      :class="[
        'inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150',
        modelValue === tab.key
          ? 'bg-white text-slate-900 shadow-xs'
          : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50',
      ]"
      @click="emit('update:modelValue', tab.key)"
    >
      <component :is="tab.icon" v-if="tab.icon" class="w-3.5 h-3.5 shrink-0" />
      <span>{{ tab.label }}</span>
      <span
        v-if="tab.count !== undefined"
        :class="[
          'text-[10px] px-1.5 py-0.2 rounded-full font-bold',
          modelValue === tab.key ? 'bg-slate-100 text-slate-700' : 'bg-slate-200/80 text-slate-500',
        ]"
      >
        {{ tab.count }}
      </span>
    </button>
  </div>
</template>
