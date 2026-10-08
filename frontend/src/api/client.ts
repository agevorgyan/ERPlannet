import axios, { AxiosError, AxiosInstance, InternalAxiosRequestConfig } from 'axios'

export interface ApiErrorResponse {
  message: string
  errors?: Record<string, string[]>
  code?: string
  status?: number
}

const baseURL = import.meta.env.VITE_API_BASE_URL || '/api/v1'

export const apiClient: AxiosInstance = axios.create({
  baseURL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  timeout: 20000,
})

// Request Interceptor
apiClient.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    // 1. Auth Token
    const token = localStorage.getItem('erplannet_token')
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`
    }

    // 2. Tenant Context resolution (Header or Subdomain)
    const tenantSlug = localStorage.getItem('erplannet_tenant_slug')
    if (tenantSlug && config.headers && !config.headers['X-Tenant-Slug']) {
      config.headers['X-Tenant-Slug'] = tenantSlug
    }

    // 3. Current Locale
    const locale = localStorage.getItem('erplannet_locale') || 'hy'
    if (config.headers) {
      config.headers['Accept-Language'] = locale
    }

    return config
  },
  (error) => Promise.reject(error)
)

// Response Interceptor
apiClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError<ApiErrorResponse>) => {
    const originalRequest = error.config as InternalAxiosRequestConfig & { _retryCount?: number }

    // 1. Session Expiry / 401 Unauthorized handling
    if (error.response?.status === 401) {
      localStorage.removeItem('erplannet_token')
      localStorage.removeItem('erplannet_user')
      if (!window.location.pathname.startsWith('/login') && !window.location.pathname.startsWith('/register')) {
        window.location.href = `/login?redirect=${encodeURIComponent(window.location.pathname)}`
      }
      return Promise.reject(error)
    }

    // 2. Safe Retry logic: ONLY for safe idempotent HTTP methods (GET, HEAD, OPTIONS)
    const safeMethods = ['get', 'head', 'options']
    const isSafeMethod = originalRequest?.method && safeMethods.includes(originalRequest.method.toLowerCase())
    const isNetworkOrServerTimeout = !error.response || [502, 503, 504].includes(error.response.status)

    if (isSafeMethod && isNetworkOrServerTimeout && originalRequest) {
      originalRequest._retryCount = originalRequest._retryCount || 0
      if (originalRequest._retryCount < 2) {
        originalRequest._retryCount++
        const delayMs = originalRequest._retryCount * 1000
        await new Promise((resolve) => setTimeout(resolve, delayMs))
        return apiClient(originalRequest)
      }
    }

    // Normalize error payload
    const normalizedError = {
      status: error.response?.status || 500,
      message: error.response?.data?.message || error.message || 'An unexpected error occurred.',
      errors: error.response?.data?.errors || {},
      raw: error,
    }

    return Promise.reject(normalizedError)
  }
)
