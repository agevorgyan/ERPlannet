<script setup lang="ts">
import { watch, onMounted, onUnmounted } from 'vue'
import { X } from 'lucide-vue-next'

export interface DrawerProps {
  modelValue: boolean
  title?: string
  position?: 'left' | 'right'
  width?: string
}

const props = withDefaults(defineProps<DrawerProps>(), {
  modelValue: false,
  position: 'right',
  width: 'max-w-md',
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'close'): void
}>()

function close() {
  emit('update:modelValue', false)
  emit('close')
}

function handleKeyDown(e: KeyboardEvent) {
  if (e.key === 'Escape' && props.modelValue) {
    close()
  }
}

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      document.body.style.overflow = 'hidden'
    } else {
      document.body.style.overflow = ''
    }
  }
)

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown)
  document.body.style.overflow = ''
})
</script>

<template>
  <Teleport to="body">
    <div v-if="modelValue" class="fixed inset-0 z-50 overflow-hidden">
      <!-- Backdrop -->
      <Transition
        enter-active-class="transition-opacity duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs"
          @click="close"
        />
      </Transition>

      <div
        :class="[
          'fixed inset-y-0 flex max-w-full',
          position === 'left' ? 'left-0' : 'right-0',
        ]"
      >
        <Transition
          enter-active-class="transform transition ease-in-out duration-300 sm:duration-400"
          :enter-from-class="position === 'left' ? '-translate-x-full' : 'translate-x-full'"
          enter-to-class="translate-x-0"
          leave-active-class="transform transition ease-in-out duration-300 sm:duration-400"
          leave-to-class="translate-x-0"
          :leave-from-class="position === 'left' ? '-translate-x-full' : 'translate-x-full'"
        >
          <div
            :class="[
              'w-screen bg-white shadow-2xl flex flex-col',
              width,
            ]"
          >
            <!-- Drawer Header -->
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
              <slot name="header">
                <h3 class="text-base font-bold text-slate-800">
                  {{ title }}
                </h3>
              </slot>
              <button
                type="button"
                class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                @click="close"
              >
                <X class="w-5 h-5" />
              </button>
            </div>

            <!-- Drawer Content -->
            <div class="flex-1 overflow-y-auto p-6">
              <slot />
            </div>

            <!-- Drawer Footer -->
            <div v-if="$slots.footer" class="p-4 border-t border-slate-100 bg-slate-50/70">
              <slot name="footer" />
            </div>
          </div>
        </Transition>
      </div>
    </div>
  </Teleport>
</template>
