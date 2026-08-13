export type UserRole = 'student' | 'teacher' | 'admin'

export type AuthUser = {
  id: string
  name: string
  username: string
  email: string
  role: UserRole
  initials: string
  school: string
  subtitle: string
}

export type LoginPayload = {
  identifier: string
  password: string
  role: UserRole
}
