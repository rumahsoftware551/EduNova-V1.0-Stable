const API_ORIGIN = String(import.meta.env.VITE_API_ORIGIN ?? '').replace(/\/$/, '')
const API_BASE_URL = `${API_ORIGIN}/api/v1`

type RequestOptions = RequestInit

export class ApiError extends Error {
  status: number
  data: unknown

  constructor(message: string, status: number, data: unknown) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.data = data
  }
}

function getCookie(name: string) {
  const encoded = `${encodeURIComponent(name)}=`
  const value = document.cookie
    .split('; ')
    .find((row) => row.startsWith(encoded))
    ?.slice(encoded.length)

  return value ? decodeURIComponent(value) : null
}

let csrfPromise: Promise<void> | null = null

export async function ensureCsrfCookie() {
  // If Sanctum already issued a token, do not make another round trip.
  if (getCookie('XSRF-TOKEN')) return
  if (csrfPromise) return csrfPromise

  csrfPromise = (async () => {
    try {
      const response = await fetch(`${API_ORIGIN}/sanctum/csrf-cookie`, {
        method: 'GET',
        credentials: 'include',
        headers: { Accept: 'application/json' },
      })

      if (!response.ok && response.status !== 204) {
        throw new ApiError('Gagal menyiapkan sesi keamanan.', response.status, null)
      }
    } catch (cause) {
      if (cause instanceof ApiError) throw cause
      throw new ApiError(
        'Backend EduNova belum terhubung. Periksa koneksi server EduNova.',
        0,
        cause,
      )
    }
  })()

  try {
    await csrfPromise
  } finally {
    csrfPromise = null
  }
}

export async function apiRequest<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const headers = new Headers(options.headers)
  headers.set('Accept', 'application/json')

  if (options.body && !(options.body instanceof FormData)) {
    headers.set('Content-Type', 'application/json')
  }

  const method = (options.method ?? 'GET').toUpperCase()
  const mutating = ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)

  if (mutating) {
    const xsrfToken = getCookie('XSRF-TOKEN')
    if (xsrfToken) headers.set('X-XSRF-TOKEN', xsrfToken)
  }

  let response: Response

  try {
    response = await fetch(`${API_BASE_URL}${path}`, {
      ...options,
      headers,
      credentials: 'include',
    })
  } catch (cause) {
    throw new ApiError(
      'Backend EduNova belum terhubung. Periksa koneksi server EduNova.',
      0,
      cause,
    )
  }

  const data = response.status === 204 ? null : await response.json().catch(() => null)

  if (!response.ok) {
    const message = typeof data === 'object' && data && 'message' in data
      ? String((data as { message?: unknown }).message ?? 'Request gagal')
      : response.status === 401
        ? 'Sesi Anda telah berakhir. Silakan login kembali.'
        : 'Request gagal'

    throw new ApiError(message, response.status, data)
  }

  return data as T
}
