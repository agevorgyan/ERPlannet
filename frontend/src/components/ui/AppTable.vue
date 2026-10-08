<script setup lang="ts">
import { computed } from 'vue'
import AppSkeleton from './AppSkeleton.vue'
import AppEmptyState from './AppEmptyState.vue'

export interface TableColumn {
  key: string
  label: string
  align?: 'left' | 'center' | 'right'
  width?: string
}

export interface TableProps {
  columns: TableColumn[]
  items: any[]
  loading?: boolean
  emptyTitle?: string
  emptyDescription?: string
}

const props = withDefaults(defineProps<TableProps>(), {
  items: () => [],
  loading: false,
  emptyTitle: 'No records found',
  emptyDescription: 'There is no data to display at this time.',
})

function getAlignClass(align?: 'left' | 'center' | 'right') {
  if (align === 'center') return 'text-center'
  if (align === 'right') return 'text-right'
  return 'text-left'
}
</script>

<template>
  <div class="w-full overflow-x-auto">
    <table class="w-full text-sm text-left border-collapse">
      <thead>
        <tr class="border-b border-slate-100 bg-slate-50/75">
          <th
            v-for="col in columns"
            :key="col.key"
            :style="col.width ? { width: col.width } : {}"
            :class="[
              'px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider',
              getAlignClass(col.align),
            ]"
          >
            {{ col.label }}
          </th>
        </tr>
      </thead>

      <tbody v-if="loading" class="divide-y divide-slate-100">
        <tr v-for="i in 5" :key="`loading-${i}`" class="animate-pulse">
          <td v-for="col in columns" :key="col.key" class="px-6 py-4">
            <AppSkeleton height="1.25rem" />
          </td>
        </tr>
      </tbody>

      <tbody v-else-if="items.length > 0" class="divide-y divide-slate-100 bg-white">
        <tr
          v-for="(item, index) in items"
          :key="item.id || index"
          class="hover:bg-slate-50/70 transition-colors group"
        >
          <td
            v-for="col in columns"
            :key="col.key"
            :class="[
              'px-6 py-4 text-slate-700 whitespace-nowrap text-sm',
              getAlignClass(col.align),
            ]"
          >
            <slot :name="`cell-${col.key}`" :item="item" :value="item[col.key]" :index="index">
              {{ item[col.key] !== undefined && item[col.key] !== null ? item[col.key] : '—' }}
            </slot>
          </td>
        </tr>
      </tbody>

      <tbody v-else>
        <tr>
          <td :colspan="columns.length" class="py-12 text-center">
            <slot name="empty">
              <AppEmptyState
                :title="emptyTitle"
                :description="emptyDescription"
              />
            </slot>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
