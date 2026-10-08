import { createI18n } from 'vue-i18n'
import hy from './locales/hy.json'
import en from './locales/en.json'
import ru from './locales/ru.json'

const savedLocale = localStorage.getItem('erplannet_locale') || 'hy'

export const i18n = createI18n({
  legacy: false,
  locale: savedLocale,
  fallbackLocale: 'en',
  messages: {
    hy,
    en,
    ru,
  },
})
