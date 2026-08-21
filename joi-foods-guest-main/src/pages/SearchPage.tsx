import { useState, useEffect, useCallback, useRef } from 'react'
import { useNavigate } from 'react-router-dom'

import backArrowIcon from '../assets/icons/back-arrow.svg'
import searchLineIcon from '../assets/icons/search-line.svg'
import noResultsImg from '../assets/images/no-results.svg'

import { useStore } from '../contexts/StoreContext'
import { searchProducts } from '../api/catalog'
import { addToCart, incrementCart, decrementCart } from '../api/cart'
import type { Product } from '../types/api'
import ProductCard from '../components/ProductCard'

const HISTORY_KEY = 'joi_search_history'
const MAX_HISTORY = 10

function getSearchHistory(): string[] {
  try {
    return JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]')
  } catch {
    return []
  }
}

function addToHistory(keyword: string) {
  const history = getSearchHistory().filter((h) => h !== keyword)
  history.unshift(keyword)
  localStorage.setItem(HISTORY_KEY, JSON.stringify(history.slice(0, MAX_HISTORY)))
}

function removeFromHistory(keyword: string) {
  const history = getSearchHistory().filter((h) => h !== keyword)
  localStorage.setItem(HISTORY_KEY, JSON.stringify(history))
}

function clearHistory() {
  localStorage.removeItem(HISTORY_KEY)
}

export default function SearchPage() {
  const navigate = useNavigate()
  const { storeCode, saveSession, refreshCartCount } = useStore()
  const inputRef = useRef<HTMLInputElement>(null)

  const [query, setQuery] = useState('')
  const [products, setProducts] = useState<Product[]>([])
  const [searched, setSearched] = useState(false)
  const [loading, setLoading] = useState(false)
  const [history, setHistory] = useState<string[]>(getSearchHistory)

  // Auto-focus input on mount
  useEffect(() => {
    inputRef.current?.focus()
  }, [])

  // Debounced search
  const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null)

  useEffect(() => {
    if (!query.trim()) {
      setSearched(false)
      setProducts([])
      return
    }

    if (timerRef.current) clearTimeout(timerRef.current)
    timerRef.current = setTimeout(() => {
      performSearch(query.trim())
    }, 500)

    return () => {
      if (timerRef.current) clearTimeout(timerRef.current)
    }
  }, [query, storeCode]) // eslint-disable-line react-hooks/exhaustive-deps

  const performSearch = async (keyword: string) => {
    if (!storeCode || !keyword) return
    setLoading(true)
    setSearched(true)
    try {
      const data = await searchProducts(storeCode, keyword)
      setProducts(data.products)
      if (data.products.length > 0) {
        addToHistory(keyword)
        setHistory(getSearchHistory())
      }
    } catch (err) {
      console.error('Search failed:', err)
    } finally {
      setLoading(false)
    }
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    if (query.trim()) {
      if (timerRef.current) clearTimeout(timerRef.current)
      performSearch(query.trim())
    }
  }

  const handleHistoryTap = (keyword: string) => {
    setQuery(keyword)
    performSearch(keyword)
  }

  const handleRemoveHistory = (keyword: string) => {
    removeFromHistory(keyword)
    setHistory(getSearchHistory())
  }

  const handleClearAll = () => {
    clearHistory()
    setHistory([])
  }

  const handleClearSearch = () => {
    setQuery('')
    setSearched(false)
    setProducts([])
    inputRef.current?.focus()
  }

  // Cart actions
  const handleAddToCart = useCallback(async (productId: number) => {
    try {
      const data = await addToCart(storeCode, productId)
      if (data.is_new_session) saveSession(data.session_id)
      setProducts((prev) =>
        prev.map((p) =>
          p.id === productId
            ? { ...p, is_in_cart: true, cart_id: data.cart_id, cart_quantity: data.quantity }
            : p,
        ),
      )
      refreshCartCount()
    } catch (err) {
      console.error('Add to cart failed:', err)
    }
  }, [storeCode, saveSession, refreshCartCount])

  const handleIncrement = useCallback(async (cartId: number) => {
    try {
      const data = await incrementCart(cartId)
      setProducts((prev) =>
        prev.map((p) =>
          p.cart_id === cartId ? { ...p, cart_quantity: data.quantity } : p,
        ),
      )
      refreshCartCount()
    } catch (err) {
      console.error('Increment failed:', err)
    }
  }, [refreshCartCount])

  const handleDecrement = useCallback(async (cartId: number) => {
    try {
      const data = await decrementCart(cartId)
      setProducts((prev) =>
        prev.map((p) =>
          p.cart_id === cartId
            ? { ...p, cart_quantity: data.quantity, is_in_cart: !data.removed, cart_id: data.removed ? null : p.cart_id }
            : p,
        ),
      )
      refreshCartCount()
    } catch (err) {
      console.error('Decrement failed:', err)
    }
  }, [refreshCartCount])

  const handleProductTap = useCallback(
    (productId: number) => navigate(`/${storeCode}/product/${productId}`),
    [navigate, storeCode],
  )

  // Show recent history when no search is active
  const showHistory = !searched && !query.trim()

  return (
    <div className="bg-white min-h-screen font-poppins">
      {/* Header */}
      <header className="sticky top-0 z-50 bg-white border-b border-[#e7e7e7] px-4 pt-3 pb-3">
        <div className="flex items-center gap-3">
          <button
            onClick={() => navigate(`/${storeCode}`)}
            className="w-8 h-8 rounded-full border border-border flex items-center justify-center shrink-0"
          >
            <img src={backArrowIcon} alt="Back" className="w-5 h-5" />
          </button>
          <form onSubmit={handleSubmit} className="flex-1 flex items-center gap-3 h-[38px]">
            <img src={searchLineIcon} alt="" className="w-5 h-5 shrink-0" />
            <input
              ref={inputRef}
              type="text"
              placeholder="Search..."
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              className="flex-1 text-[14px] text-dark placeholder-[#8f8f8f] outline-none bg-transparent"
            />
          </form>
          {query && (
            <button onClick={handleClearSearch} className="shrink-0 w-6 h-6 flex items-center justify-center">
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M1 1L13 13M1 13L13 1" stroke="#888888" strokeWidth="1.5" strokeLinecap="round" />
              </svg>
            </button>
          )}
        </div>
      </header>

      <div className="px-4">
        {/* Recent History */}
        {showHistory && history.length > 0 && (
          <div className="mt-4">
            <div className="flex items-center justify-between mb-3">
              <p className="text-[16px] font-semibold text-dark">Recent history</p>
              <button onClick={handleClearAll} className="text-[14px] font-medium text-muted">
                Clear all
              </button>
            </div>
            <div className="bg-white rounded-[10px]">
              {history.map((keyword, i) => (
                <div
                  key={keyword}
                  className={`flex items-center justify-between py-3 ${
                    i < history.length - 1 ? 'border-b border-[#f1f1f1]' : ''
                  }`}
                >
                  <button
                    onClick={() => handleHistoryTap(keyword)}
                    className="flex items-center gap-3 flex-1 min-w-0"
                  >
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" className="shrink-0">
                      <path d="M8 14C11.3137 14 14 11.3137 14 8C14 4.68629 11.3137 2 8 2C4.68629 2 2 4.68629 2 8C2 11.3137 4.68629 14 8 14Z" stroke="#888888" strokeWidth="1.2" />
                      <path d="M8 5V8L10 10" stroke="#888888" strokeWidth="1.2" strokeLinecap="round" />
                    </svg>
                    <span className="text-[12px] text-dark leading-[22px] truncate">{keyword}</span>
                  </button>
                  <button
                    onClick={() => handleRemoveHistory(keyword)}
                    className="shrink-0 w-6 h-6 flex items-center justify-center"
                  >
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                      <path d="M1 1L11 11M1 11L11 1" stroke="#888888" strokeWidth="1.2" strokeLinecap="round" />
                    </svg>
                  </button>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Loading */}
        {loading && (
          <div className="flex items-center justify-center py-12">
            <p className="text-muted text-[14px]">Searching...</p>
          </div>
        )}

        {/* No Results */}
        {searched && !loading && products.length === 0 && (
          <div className="flex flex-col items-center justify-center pt-24">
            <img src={noResultsImg} alt="No results" className="w-[256px] h-[256px] object-contain" />
            <p className="text-[14px] text-body mt-4 text-center">
              No results found for your search.
            </p>
          </div>
        )}

        {/* Search Results - same grid as ProductsListPage */}
        {searched && !loading && products.length > 0 && (
          <div className="mt-4 pb-8">
            <div className="grid grid-cols-2 sm:grid-cols-3 gap-4">
              {products.map((product) => (
                <div key={product.id}>
                  <ProductCard
                    product={product}
                    onAddToCart={handleAddToCart}
                    onIncrement={handleIncrement}
                    onDecrement={handleDecrement}
                    onTap={handleProductTap}
                  />
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
