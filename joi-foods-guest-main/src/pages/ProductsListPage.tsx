import { useState, useEffect, useCallback, useRef } from 'react'
import { useParams, useNavigate } from 'react-router-dom'

import backArrowIcon from '../assets/icons/back-arrow.svg'
import searchIcon from '../assets/icons/search.svg'
import cartIcon from '../assets/icons/cart.svg'

import { useStore } from '../contexts/StoreContext'
import { getProducts } from '../api/catalog'
import { addToCart, incrementCart, decrementCart } from '../api/cart'
import type { Product } from '../types/api'
import ProductCard from '../components/ProductCard'

export default function ProductsListPage() {
  const { categoryId } = useParams<{ categoryId: string }>()
  const navigate = useNavigate()
  const { storeCode, cartCount, saveSession, refreshCartCount } = useStore()

  const [products, setProducts] = useState<Product[]>([])
  const [loading, setLoading] = useState(true)
  const [loadingMore, setLoadingMore] = useState(false)
  const [page, setPage] = useState(1)
  const [hasNext, setHasNext] = useState(false)
  const [categoryName, setCategoryName] = useState('')

  const sentinelRef = useRef<HTMLDivElement>(null)

  // Fetch products
  const fetchProducts = useCallback(async (pageNum: number, append: boolean) => {
    if (!storeCode || !categoryId) return

    if (pageNum === 1) setLoading(true)
    else setLoadingMore(true)

    try {
      const data = await getProducts(storeCode, Number(categoryId), pageNum, 20)
      setProducts((prev) => append ? [...prev, ...data.products] : data.products)
      setHasNext(data.pagination.has_next)
      setPage(pageNum)
      // Get category name from first product
      if (data.products.length > 0 && !categoryName) {
        setCategoryName(data.products[0].category.name)
      }
    } catch (err) {
      console.error('Failed to load products:', err)
    } finally {
      setLoading(false)
      setLoadingMore(false)
    }
  }, [storeCode, categoryId, categoryName])

  // Initial load
  useEffect(() => {
    setProducts([])
    setPage(1)
    setHasNext(false)
    setCategoryName('')
    fetchProducts(1, false)
  }, [storeCode, categoryId]) // eslint-disable-line react-hooks/exhaustive-deps

  // Infinite scroll - IntersectionObserver on sentinel
  useEffect(() => {
    if (!hasNext || loadingMore) return

    const observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting && hasNext && !loadingMore) {
          fetchProducts(page + 1, true)
        }
      },
      { threshold: 0.1 },
    )

    const el = sentinelRef.current
    if (el) observer.observe(el)

    return () => {
      if (el) observer.unobserve(el)
    }
  }, [hasNext, loadingMore, page, fetchProducts])

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

  return (
    <div className="bg-white min-h-screen font-poppins">
      {/* Header */}
      <header className="sticky top-0 z-50 bg-white border-b border-[#e7e7e7] px-4 pt-3 pb-3">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <button
              onClick={() => navigate(`/${storeCode}`)}
              className="w-8 h-8 rounded-full border border-border flex items-center justify-center"
            >
              <img src={backArrowIcon} alt="Back" className="w-5 h-5" />
            </button>
            <h1 className="text-[20px] font-semibold text-dark leading-normal">
              {categoryName || 'Products'}
            </h1>
          </div>
          <div className="flex items-center gap-[10px]">
            <button onClick={() => navigate(`/${storeCode}/search`)} className="w-[42px] h-[42px] shrink-0">
              <img src={searchIcon} alt="Search" className="w-full h-full" />
            </button>
            <button
              onClick={() => navigate(`/${storeCode}/cart`)}
              className="w-[42px] h-[42px] shrink-0 rounded-full border border-border bg-white flex items-center justify-center relative"
            >
              <img src={cartIcon} alt="Cart" className="w-6 h-6" />
              {cartCount > 0 && (
                <span className="absolute -top-1 -right-1 min-w-[18px] h-[18px] rounded-full bg-orange text-white text-[10px] font-bold flex items-center justify-center px-1">
                  {cartCount}
                </span>
              )}
            </button>
          </div>
        </div>
      </header>

      {/* Products Grid */}
      <div className="px-4 mt-4 pb-8">
        {loading ? (
          <div className="grid grid-cols-2 sm:grid-cols-3 gap-4">
            {Array.from({ length: 6 }).map((_, i) => (
              <div key={i} className="bg-white rounded-xl border border-border overflow-hidden">
                <div className="h-[177px] bg-gray-200 animate-pulse" />
                <div className="px-[11px] pt-[10px] pb-[12px]">
                  <div className="h-4 bg-gray-200 animate-pulse rounded w-3/4" />
                  <div className="h-3 bg-gray-200 animate-pulse rounded w-1/3 mt-2" />
                  <div className="flex items-end justify-between mt-3">
                    <div className="h-4 bg-gray-200 animate-pulse rounded w-1/3" />
                    <div className="w-8 h-8 bg-gray-200 animate-pulse rounded-full" />
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : products.length === 0 ? (
          <div className="flex items-center justify-center py-12">
            <p className="text-muted text-[14px]">No products available</p>
          </div>
        ) : (
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
        )}

        {/* Infinite scroll sentinel */}
        {hasNext && (
          <div ref={sentinelRef} className="flex items-center justify-center py-6">
            {loadingMore && <p className="text-muted text-[14px]">Loading more...</p>}
          </div>
        )}
      </div>
    </div>
  )
}
