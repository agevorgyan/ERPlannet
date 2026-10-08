import { defineStore } from 'pinia'
import { ref } from 'vue'

export interface ToastItem {
  id: string
  title?: string
  message: string
  type: 'success' | 'error' | 'warning' | 'info'
  duration?: number
}

export interface ConfirmDialogOptions {
  title: string
  message: string
  confirmText?: string
  cancelText?: string
  type?: 'danger' | 'warning' | 'info'
  onConfirm: () => void | Promise<void>
  onCancel?: () => void
}

export const useUiStore = defineStore('ui', () => {
  // Sidebar state
  const isSidebarCollapsed = ref<boolean>(localStorage.getItem('erplannet_sidebar_collapsed') === 'true')
  const isMobileDrawerOpen = ref<boolean>(false)

  // Global search modal (Cmd+K)
  const isSearchOpen = ref<boolean>(false)

  // Notifications dropdown
  const isNotificationsOpen = ref<boolean>(false)
  const unreadNotificationsCount = ref<number>(4)

  // Toasts
  const toasts = ref<ToastItem[]>([])

  // Global Confirmation dialog
  const isConfirmDialogOpen = ref<boolean>(false)
  const confirmDialogOptions = ref<ConfirmDialogOptions | null>(null)
  const isConfirmLoading = ref<boolean>(false)

  // Actions
  function toggleSidebar() {
    isSidebarCollapsed.value = !isSidebarCollapsed.value
    localStorage.setItem('erplannet_sidebar_collapsed', String(isSidebarCollapsed.value))
  }

  function setSidebarCollapsed(collapsed: boolean) {
    isSidebarCollapsed.value = collapsed
    localStorage.setItem('erplannet_sidebar_collapsed', String(collapsed))
  }

  function toggleMobileDrawer() {
    isMobileDrawerOpen.value = !isMobileDrawerOpen.value
  }

  function openSearch() {
    isSearchOpen.value = true
  }

  function closeSearch() {
    isSearchOpen.value = false
  }

  function toggleNotifications() {
    isNotificationsOpen.value = !isNotificationsOpen.value
  }

  // Toast notifications
  function addToast(toast: Omit<ToastItem, 'id'>) {
    const id = `toast_${Date.now()}_${Math.random().toString(36).substring(2, 9)}`
    const duration = toast.duration ?? 4000
    const newToast: ToastItem = { ...toast, id, duration }
    toasts.value.push(newToast)

    if (duration > 0) {
      setTimeout(() => {
        removeToast(id)
      }, duration)
    }
    return id
  }

  function removeToast(id: string) {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }

  function success(message: string, title?: string) {
    return addToast({ type: 'success', message, title })
  }

  function error(message: string, title?: string) {
    return addToast({ type: 'error', message, title })
  }

  function warning(message: string, title?: string) {
    return addToast({ type: 'warning', message, title })
  }

  function info(message: string, title?: string) {
    return addToast({ type: 'info', message, title })
  }

  // Confirmation dialog
  function confirm(options: ConfirmDialogOptions) {
    confirmDialogOptions.value = options
    isConfirmDialogOpen.value = true
  }

  async function handleConfirm() {
    if (!confirmDialogOptions.value) return
    isConfirmLoading.value = true
    try {
      await confirmDialogOptions.value.onConfirm()
      isConfirmDialogOpen.value = false
    } finally {
      isConfirmLoading.value = false
    }
  }

  function handleCancel() {
    if (confirmDialogOptions.value?.onCancel) {
      confirmDialogOptions.value.onCancel()
    }
    isConfirmDialogOpen.value = false
    confirmDialogOptions.value = null
  }

  return {
    isSidebarCollapsed,
    isMobileDrawerOpen,
    isSearchOpen,
    isNotificationsOpen,
    unreadNotificationsCount,
    toasts,
    isConfirmDialogOpen,
    confirmDialogOptions,
    isConfirmLoading,
    toggleSidebar,
    setSidebarCollapsed,
    toggleMobileDrawer,
    openSearch,
    closeSearch,
    toggleNotifications,
    addToast,
    removeToast,
    success,
    error,
    warning,
    info,
    confirm,
    handleConfirm,
    handleCancel,
  }
})
