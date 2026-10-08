<script setup lang="ts">
export interface CardProps {
  title?: string
  subtitle?: string
  noPadding?: boolean
}

withDefaults(defineProps<CardProps>(), {
  noPadding: false,
})
</script>

<template>
  <div class="bg-white rounded-2xl border border-slate-100 shadow-soft overflow-hidden transition-all duration-150">
    <div
      v-if="title || subtitle || $slots.header || $slots.actions"
      class="px-6 py-5 border-b border-slate-100 flex items-center justify-between gap-4"
    >
      <slot name="header">
        <div class="flex flex-col text-left">
          <h3 v-if="title" class="text-base font-bold text-slate-800 tracking-tight">
            {{ title }}
          </h3>
          <p v-if="subtitle" class="text-xs text-slate-400 mt-0.5">
            {{ subtitle }}
          </p>
        </div>
      </slot>
      <div v-if="$slots.actions" class="flex items-center gap-2 shrink-0">
        <slot name="actions" />
      </div>
    </div>

    <div :class="noPadding ? '' : 'p-6'">
      <slot />
    </div>

    <div v-if="$slots.footer" class="px-6 py-4 bg-slate-50/60 border-t border-slate-100">
      <slot name="footer" />
    </div>
  </div>
</template>
