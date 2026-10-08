<script setup lang="ts">
export interface SkeletonProps {
  width?: string
  height?: string
  circle?: boolean
  rounded?: string
}

withDefaults(defineProps<SkeletonProps>(), {
  width: '100%',
  height: '1rem',
  circle: false,
  rounded: 'rounded-lg',
})
</script>

<template>
  <div
    :class="[
      'bg-slate-200/70 animate-pulse',
      circle ? 'rounded-full' : rounded,
    ]"
    :style="{
      width: circle ? height : width,
      height: height,
    }"
  />
</template>
