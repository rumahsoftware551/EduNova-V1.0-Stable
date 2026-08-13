import type { AuthUser } from '@/features/auth/types'

export type LoginResponse = {
  message: string
  user: AuthUser
}

export type ApiMeta = {
  current_page: number
  per_page: number
  total: number
  last_page: number
}

export type Paginated<T> = {
  data: T[]
  meta: ApiMeta
}
