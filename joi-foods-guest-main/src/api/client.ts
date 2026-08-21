import { getGuestSession } from '../utils/session'
import type { ApiResponse } from '../types/api'

const BASE_URL = import.meta.env.VITE_API_BASE_URL
const API_KEY = import.meta.env.VITE_API_KEY

export class ApiError extends Error {
  status: number
  data: unknown

  constructor(status: number, message: string, data?: unknown) {
    super(message)
    this.status = status
    this.data = data
  }
}

export async function apiPost<T>(
  endpoint: string,
  body?: Record<string, string | number | undefined>,
): Promise<T> {
  const headers: Record<string, string> = {
    'Content-Type': 'application/x-www-form-urlencoded',
    Auth: API_KEY,
  }

  const session = getGuestSession()
  if (session) {
    headers['X-Guest-Session'] = session
  }

  const params = new URLSearchParams()
  if (body) {
    for (const [key, value] of Object.entries(body)) {
      if (value !== undefined && value !== null) {
        params.append(key, String(value))
      }
    }
  }

  let res: Response
  try {
    res = await fetch(`${BASE_URL}${endpoint}`, {
      method: 'POST',
      headers,
      body: params,
    })
  } catch {
    throw new ApiError(0, 'Network error. Please check your connection.')
  }

  let json: ApiResponse<T>
  try {
    json = await res.json()
  } catch {
    throw new ApiError(res.status, `Server error (${res.status})`)
  }

  if (!res.ok || !json.success) {
    throw new ApiError(
      json.status || res.status,
      json.message || `Request failed (${res.status})`,
      json.data,
    )
  }

  return json.data as T
}
