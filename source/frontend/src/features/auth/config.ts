import type { UserRole } from './types'

export const ROLE_LABEL: Record<UserRole, string> = {
  student: 'Siswa',
  teacher: 'Guru',
  admin: 'Admin',
}

export const ROLE_HOME: Record<UserRole, string> = {
  student: '/student',
  teacher: '/teacher',
  admin: '/admin',
}

const DEMO_IDENTIFIERS: Record<UserRole, string> = {
  student: 'andi.saputra',
  teacher: 'susanto',
  admin: 'admin',
}

export function demoIdentifierForRole(role: UserRole) {
  return DEMO_IDENTIFIERS[role]
}
