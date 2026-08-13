import type { ReactNode } from 'react'
import { Navigate, Outlet, useLocation } from 'react-router'
import { ROLE_HOME } from './config'
import { useAuth } from './AuthContext'
import type { UserRole } from './types'

function AuthLoadingScreen() {
  return <div className="grid min-h-screen place-items-center bg-[#F6F8FB] px-6">
    <div className="text-center">
      <div className="mx-auto h-9 w-9 animate-spin rounded-full border-2 border-slate-200 border-t-blue-600" />
      <p className="mt-4 text-sm font-medium text-slate-600">Menyiapkan workspace EduNova...</p>
    </div>
  </div>
}

export function RequireAuth() {
  const { isAuthenticated, isLoading } = useAuth()
  const location = useLocation()

  if (isLoading) return <AuthLoadingScreen />

  if (!isAuthenticated) {
    return <Navigate to="/login" replace state={{ from: location.pathname }} />
  }

  return <Outlet />
}

export function RequireRole({ role, children }: { role: UserRole; children: ReactNode }) {
  const { user, isLoading } = useAuth()
  if (isLoading) return <AuthLoadingScreen />
  if (!user) return <Navigate to="/login" replace />
  if (user.role !== role) return <Navigate to={ROLE_HOME[user.role]} replace />
  return children
}

export function PublicOnly({ children }: { children: ReactNode }) {
  const { user, isLoading } = useAuth()
  if (isLoading) return <AuthLoadingScreen />
  if (user) return <Navigate to={ROLE_HOME[user.role]} replace />
  return children
}

export function RootRedirect() {
  const { user, isLoading } = useAuth()
  if (isLoading) return <AuthLoadingScreen />
  return <Navigate to={user ? ROLE_HOME[user.role] : '/login'} replace />
}
