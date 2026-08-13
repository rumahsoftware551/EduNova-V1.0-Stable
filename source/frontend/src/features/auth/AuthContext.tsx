import { createContext, useContext, useEffect, useMemo, useState, type ReactNode } from 'react'
import { ApiError } from '@/lib/api/client'
import { authApi } from './api'
import type { AuthUser, LoginPayload } from './types'

type AuthStatus = 'loading' | 'authenticated' | 'guest'

type AuthContextValue = {
  user: AuthUser | null
  status: AuthStatus
  isLoading: boolean
  isAuthenticated: boolean
  login: (payload: LoginPayload) => Promise<AuthUser>
  logout: () => Promise<void>
  refreshUser: () => Promise<void>
}

const AuthContext = createContext<AuthContextValue | null>(null)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null)
  const [status, setStatus] = useState<AuthStatus>('loading')

  async function refreshUser() {
    try {
      const response = await authApi.me()
      setUser(response.user)
      setStatus('authenticated')
    } catch (cause) {
      if (cause instanceof ApiError && cause.status !== 401 && cause.status !== 419 && cause.status !== 0) {
        console.error('EduNova auth bootstrap failed', cause)
      }
      setUser(null)
      setStatus('guest')
    }
  }

  useEffect(() => {
    void refreshUser()
  }, [])

  async function login(payload: LoginPayload) {
    const response = await authApi.login(payload)
    setUser(response.user)
    setStatus('authenticated')
    return response.user
  }

  async function logout() {
    try {
      await authApi.logout()
    } finally {
      setUser(null)
      setStatus('guest')
    }
  }

  const value = useMemo<AuthContextValue>(() => ({
    user,
    status,
    isLoading: status === 'loading',
    isAuthenticated: status === 'authenticated' && Boolean(user),
    login,
    logout,
    refreshUser,
  }), [user, status])

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const value = useContext(AuthContext)
  if (!value) throw new Error('useAuth must be used inside AuthProvider')
  return value
}
