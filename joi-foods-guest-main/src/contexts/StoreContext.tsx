import { createContext, useContext, useState, useEffect, useCallback, type ReactNode } from 'react'
import { useParams } from 'react-router-dom'
import { getStoreInfo } from '../api/home'
import { getCartCount } from '../api/cart'
import { ApiError } from '../api/client'
import { getGuestSession, setGuestSession } from '../utils/session'
import type { Store } from '../types/api'

import logo from '../assets/images/logo.png'

interface StoreContextValue {
  storeCode: string
  store: Store | null
  loading: boolean
  error: string | null
  sessionId: string | null
  cartCount: number
  saveSession: (id: string) => void
  refreshCartCount: () => Promise<void>
}

const StoreContext = createContext<StoreContextValue | null>(null)

function InvalidStoreScreen({ message, status }: { message: string; status?: number }) {
  const title =
    status === 404
      ? 'Store Not Found'
      : status === 403
        ? 'Not Available'
        : status === 400
          ? 'Store Unavailable'
          : 'Something Went Wrong'

  return (
    <div className="bg-white min-h-screen font-poppins flex flex-col items-center justify-center px-6 text-center">
      <img src={logo} alt="JOI Foods" className="w-[72px] h-[72px] rounded-full mb-6" />
      <p className="text-[20px] font-semibold text-dark mb-2">{title}</p>
      <p className="text-[14px] text-muted leading-6 max-w-[300px]">{message}</p>
    </div>
  )
}

function LoadingScreen() {
  return (
    <div className="bg-white min-h-screen font-poppins flex flex-col items-center justify-center">
      <img src={logo} alt="JOI Foods" className="w-[56px] h-[56px] rounded-full mb-4 animate-pulse" />
      <p className="text-muted text-[14px]">Loading store...</p>
    </div>
  )
}

export function StoreProvider({ children }: { children: ReactNode }) {
  const { storeCode } = useParams<{ storeCode: string }>()
  const code = storeCode || ''

  const [store, setStore] = useState<Store | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [errorStatus, setErrorStatus] = useState<number | undefined>()
  const [sessionId, setSessionId] = useState<string | null>(getGuestSession)
  const [cartCount, setCartCount] = useState(0)

  // Validate store on mount / storeCode change
  useEffect(() => {
    if (!code) {
      setError('No store code provided. Please scan a valid QR code.')
      setErrorStatus(400)
      setLoading(false)
      return
    }

    setLoading(true)
    setError(null)
    setErrorStatus(undefined)

    getStoreInfo(code)
      .then((data) => {
        setStore(data.store)
        setLoading(false)
      })
      .catch((err) => {
        if (err instanceof ApiError) {
          setError(err.message)
          setErrorStatus(err.status)
        } else {
          setError('Failed to connect to server. Please try again.')
          setErrorStatus(0)
        }
        setLoading(false)
      })
  }, [code])

  // Fetch cart count on mount if session exists
  useEffect(() => {
    if (code && sessionId && store) {
      getCartCount(code)
        .then((data) => setCartCount(data.count))
        .catch(() => setCartCount(0))
    }
  }, [code, sessionId, store])

  const saveSession = useCallback((id: string) => {
    setGuestSession(id)
    setSessionId(id)
  }, [])

  const refreshCartCount = useCallback(async () => {
    if (!code || !sessionId) return
    try {
      const data = await getCartCount(code)
      setCartCount(data.count)
    } catch {
      // Silently fail
    }
  }, [code, sessionId])

  // Show loading screen while verifying store
  if (loading) {
    return <LoadingScreen />
  }

  // Show error screen if store is invalid
  if (error) {
    return <InvalidStoreScreen message={error} status={errorStatus} />
  }

  return (
    <StoreContext.Provider
      value={{
        storeCode: code,
        store,
        loading,
        error,
        sessionId,
        cartCount,
        saveSession,
        refreshCartCount,
      }}
    >
      {children}
    </StoreContext.Provider>
  )
}

export function useStore() {
  const ctx = useContext(StoreContext)
  if (!ctx) throw new Error('useStore must be used within StoreProvider')
  return ctx
}
