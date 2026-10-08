<script setup lang="ts">
import { computed } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

export interface PaginationProps {
  currentPage: number
  totalPages: number
  totalItems?: number
  perPage?: number
}

const props = withDefaults(defineProps<PaginationProps>(), {
  currentPage: 1,
  totalPages: 1,
  totalItems: 0,
  perPage: 15,
})

const emit = defineEmits<{
  (e: 'update:currentPage', page: number): void
}>()

const fromItem = computed(() => {
  if (props.totalItems === 0) return 0
  return (props.currentPage - 1) * props.perPage + 1
})

const toItem = computed(() => {
  return Math.min(props.currentPage * props.perPage, props.totalItems || 0)
})

function setPage(page: number) {
  if (page >= 1 && page <= props.totalPages && page !== props.currentPage) {
    emit('update:currentPage', page)
  }
}
</script>

<template>
  <div class="px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 border-t border-slate-100 bg-white">
    <div v-if="totalItems !== undefined && totalItems > 0">
      Showing <span class="font-semibold text-slate-700">{{ fromItem }}</span> to
      <span class="font-semibold text-slate-700">{{ toItem }}</span> of
      <span class="font-semibold text-slate-700">{{ totalItems }}</span> entries
    </div>
    <div v-else>
      Page <span class="font-semibold text-slate-700">{{ currentPage }}</span> of
      <span class="font-semibold text-slate-700">{{ totalPages }}</span>
    </div>

    <div class="flex items-center gap-1.5">
      <button
        type="button"
        :disabled="currentPage <= 1"
        class="inline-flex items-center justify-center p-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:pointer-events-none transition-colors"
        @click="setPage(currentPage - 1)"
      >
        <ChevronLeft class="w-4 h-4" />
      </button>

      <div class="flex items-center gap-1">
        <button
          v-for="page in totalPages"
          :key="page"
          type="button"
          :class="[
            'w-8 h-8 rounded-lg text-xs font-semibold flex items-center justify-center transition-colors',
            page === currentPage
              ? 'bg-brand-600 text-white shadow-xs'
              : 'text-slate-600 hover:bg-slate-100',
          ]"
          @click="setPage(page)"
        >
          {{ page }}
        </button>
      </div>

      <button
        type="button"
        :disabled="currentPage >= totalPages"
        class="inline-flex items-center justify-center p-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:pointer-events-none transition-colors"
        @click="setPage(currentPage + 1)"
      >
        <ChevronRight class="w-4 h-4" />
      </button>
    </div>
  </div>
</template>
