import { apiRequest, ensureCsrfCookie } from '@/lib/api/client'
import type { AuthUser, LoginPayload } from './types'

type AuthResponse = {
  message?: string
  user: AuthUser
}

export const authApi = {
  async login(payload: LoginPayload) {
    await ensureCsrfCookie()
    return apiRequest<AuthResponse>('/auth/login', {
      method: 'POST',
      body: JSON.stringify(payload),
    })
  },

  async me() {
    return apiRequest<{ user: AuthUser }>('/auth/me')
  },

  async logout() {
    await ensureCsrfCookie()
    return apiRequest<{ message: string }>('/auth/logout', {
      method: 'POST',
    })
  },
}
