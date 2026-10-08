<script setup lang="ts">
import { computed } from 'vue'
import { ArrowUpRight, ArrowDownRight, Minus } from 'lucide-vue-next'

export interface StatCardProps {
  title: string
  value: string | number
  subtitle?: string
  trend?: string
  trendType?: 'up' | 'down' | 'neutral'
  iconBg?: string
  iconColor?: string
  sparklineColor?: string
  sparklinePoints?: number[]
}

const props = withDefaults(defineProps<StatCardProps>(), {
  trendType: 'up',
  iconBg: 'bg-brand-50',
  iconColor: 'text-brand-600',
  sparklineColor: '#2563eb',
  sparklinePoints: () => [12, 18, 14, 22, 19, 28, 25, 32],
})

// Build smooth SVG polyline path for sparkline
const svgPath = computed(() => {
  const points = props.sparklinePoints
  if (!points || points.length < 2) return ''
  const min = Math.min(...points)
  const max = Math.max(...points) || 1
  const height = 28
  const width = 84
  const step = width / (points.length - 1)

  return points
    .map((val, idx) => {
      const x = idx * step
      const normalized = (val - min) / (max - min || 1)
      const y = height - normalized * (height - 6) - 3
      return `${idx === 0 ? 'M' : 'L'} ${x.toFixed(1)} ${y.toFixed(1)}`
    })
    .join(' ')
})
</script>

<template>
  <div class="bg-white rounded-2xl border border-slate-100 shadow-soft p-5 flex flex-col justify-between hover:shadow-md transition-all duration-200 group">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
        {{ title }}
      </span>
      <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-transform group-hover:scale-105', iconBg, iconColor]">
        <slot name="icon" />
      </div>
    </div>

    <div class="flex items-baseline justify-between mb-3">
      <div>
        <div class="text-2xl font-extrabold text-slate-900 tracking-tight">
          {{ value }}
        </div>
        <p v-if="subtitle" class="text-xs text-slate-400 mt-0.5">
          {{ subtitle }}
        </p>
      </div>

      <!-- Sparkline mini chart -->
      <div v-if="sparklinePoints.length > 0" class="w-20 h-7 overflow-hidden shrink-0">
        <svg viewBox="0 0 84 28" class="w-full h-full stroke-[2.5] fill-none overflow-visible">
          <path
            :d="svgPath"
            :stroke="sparklineColor"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
      </div>
    </div>

    <!-- Trend footer -->
    <div v-if="trend" class="flex items-center gap-1.5 pt-2 border-t border-slate-50 text-xs">
      <span
        :class="[
          'inline-flex items-center gap-0.5 font-semibold px-1.5 py-0.5 rounded-md',
          trendType === 'up'
            ? 'text-emerald-700 bg-emerald-50'
            : trendType === 'down'
            ? 'text-rose-700 bg-rose-50'
            : 'text-slate-600 bg-slate-100',
        ]"
      >
        <ArrowUpRight v-if="trendType === 'up'" class="w-3 h-3 stroke-[2.5]" />
        <ArrowDownRight v-else-if="trendType === 'down'" class="w-3 h-3 stroke-[2.5]" />
        <Minus v-else class="w-3 h-3 stroke-[2.5]" />
        {{ trend }}
      </span>
      <span class="text-slate-400 font-normal">
        <slot name="trend-label">vs last month</slot>
      </span>
    </div>
  </div>
</template>
