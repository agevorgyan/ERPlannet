<script setup lang="ts">
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Layers, Globe, CheckCircle2 } from 'lucide-vue-next'
import AppToast from '@/components/ui/AppToast.vue'
import AppConfirmDialog from '@/components/ui/AppConfirmDialog.vue'

const { locale } = useI18n()
const langMenuOpen = ref(false)

const availableLanguages = [
  { code: 'hy', label: 'Հայերեն', flag: '🇦🇲' },
  { code: 'en', label: 'English', flag: '🇺🇸' },
  { code: 'ru', label: 'Русский', flag: '🇷🇺' },
]

function changeLanguage(langCode: string) {
  locale.value = langCode
  localStorage.setItem('erplannet_locale', langCode)
  langMenuOpen.value = false
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative selection:bg-brand-500 selection:text-white">
    <!-- Top Right Language Switcher -->
    <div class="absolute top-5 right-5 z-20">
      <div class="relative">
        <button
          type="button"
          class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white hover:bg-slate-50 rounded-xl border border-slate-200/80 shadow-2xs transition-colors"
          @click="langMenuOpen = !langMenuOpen"
        >
          <Globe class="w-4 h-4 text-slate-400" />
          <span class="uppercase font-bold">{{ locale }}</span>
        </button>

        <div
          v-if="langMenuOpen"
          class="absolute right-0 mt-2 w-36 bg-white rounded-xl shadow-lg border border-slate-100 py-1 z-30"
        >
          <button
            v-for="l in availableLanguages"
            :key="l.code"
            type="button"
            class="w-full flex items-center justify-between px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 transition-colors"
            @click="changeLanguage(l.code)"
          >
            <span class="flex items-center gap-2">
              <span>{{ l.flag }}</span>
              <span>{{ l.label }}</span>
            </span>
            <CheckCircle2 v-if="locale === l.code" class="w-3.5 h-3.5 text-brand-600" />
          </button>
        </div>
      </div>
    </div>

    <!-- Center Card & Branding -->
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
      <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-brand-600 text-white shadow-lg shadow-brand-500/25 mb-4">
        <Layers class="w-6 h-6 stroke-[2.2]" />
      </div>
      <h2 class="text-2xl font-black text-slate-900 tracking-tight">
        ERPlannet
      </h2>
      <p class="text-xs font-semibold text-brand-600 uppercase tracking-widest mt-1">
        Enterprise Cloud Platform
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-6 sm:px-10 shadow-xl shadow-slate-900/5 rounded-3xl border border-slate-100">
        <slot />
      </div>
      <div class="mt-6 text-center text-xs text-slate-400">
        &copy; 2026 ERPlannet. Universal SaaS Platform. All rights reserved.
      </div>
    </div>

    <!-- Global Toasts & Dialogs -->
    <AppToast />
    <AppConfirmDialog />
  </div>
</template>
