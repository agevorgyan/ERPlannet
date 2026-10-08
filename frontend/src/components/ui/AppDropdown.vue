<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

export interface DropdownProps {
  align?: 'left' | 'right'
  width?: string
}

withDefaults(defineProps<DropdownProps>(), {
  align: 'right',
  width: 'w-56',
})

const isOpen = ref(false)
const dropdownRef = ref<HTMLElement | null>(null)

function toggle() {
  isOpen.value = !isOpen.value
}

function close() {
  isOpen.value = false
}

function handleClickOutside(event: MouseEvent) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    close()
  }
}

onMounted(() => {
  window.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  window.removeEventListener('click', handleClickOutside)
})

defineExpose({
  open: () => (isOpen.value = true),
  close,
  toggle,
})
</script>

<template>
  <div ref="dropdownRef" class="relative inline-block text-left">
    <div @click.stop="toggle">
      <slot name="trigger" :is-open="isOpen" />
    </div>

    <Transition
      enter-active-class="transition duration-100 ease-out"
      enter-from-class="transform scale-95 opacity-0 -translate-y-1"
      enter-to-class="transform scale-100 opacity-100 translate-y-0"
      leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100 translate-y-0"
      leave-to-class="transform scale-95 opacity-0 -translate-y-1"
    >
      <div
        v-if="isOpen"
        :class="[
          'absolute z-40 mt-2 rounded-2xl bg-white shadow-xl border border-slate-100 p-1.5 focus:outline-none ring-1 ring-black/5',
          align === 'right' ? 'right-0' : 'left-0',
          width,
        ]"
        @click="close"
      >
        <slot />
      </div>
    </Transition>
  </div>
</template>
