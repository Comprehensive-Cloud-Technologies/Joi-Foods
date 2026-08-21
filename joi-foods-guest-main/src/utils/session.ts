const SESSION_KEY = 'joi_guest_session'

export function getGuestSession(): string | null {
  return localStorage.getItem(SESSION_KEY)
}

export function setGuestSession(sessionId: string): void {
  localStorage.setItem(SESSION_KEY, sessionId)
}

export function clearGuestSession(): void {
  localStorage.removeItem(SESSION_KEY)
}
