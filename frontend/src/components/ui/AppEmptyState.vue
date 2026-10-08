<script setup lang="ts">
import { Inbox } from 'lucide-vue-next'

export interface EmptyStateProps {
  title?: string
  description?: string
}

withDefaults(defineProps<EmptyStateProps>(), {
  title: 'No items yet',
  description: 'Get started by creating your first entry.',
})
</script>

<template>
  <div class="flex flex-col items-center justify-center py-12 px-4 text-center max-w-sm mx-auto">
    <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-4 border border-slate-200/50">
      <slot name="icon">
        <Inbox class="w-7 h-7 stroke-[1.5]" />
      </slot>
    </div>

    <h4 class="text-base font-bold text-slate-800">
      {{ title }}
    </h4>
    <p class="text-xs text-slate-400 mt-1 leading-relaxed">
      {{ description }}
    </p>

    <div v-if="$slots.action" class="mt-5">
      <slot name="action" />
    </div>
  </div>
</template>
