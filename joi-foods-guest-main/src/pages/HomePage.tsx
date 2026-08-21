import { useState, useRef, useCallback, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'

// Icons
import logo from '../assets/images/logo.png'
import searchIcon from '../assets/icons/search.svg'
import cartIcon from '../assets/icons/cart.svg'

// API & Context
import { useStore } from '../contexts/StoreContext'
import { getBanners, getCategories, getFeaturedProducts } from '../api/home'
import { addToCart, incrementCart, decrementCart } from '../api/cart'
import type { Banner, Category, Product } from '../types/api'

// Shared component
import ProductCard from '../components/ProductCard'

function SectionHeader({ title, onViewAll }: { title: string; onViewAll?: () => void }) {
  return (
    <div className="flex items-center justify-between mb-3">
      <h2 className="text-[16px] font-semibold text-dark leading-normal">{title}</h2>
      {onViewAll && (
        <button onClick={onViewAll} className="text-[14px] font-medium text-muted">View All</button>
      )}
    </div>
  )
}

function App() {
  const navigate = useNavigate()
  const { storeCode, store, cartCount, saveSession, refreshCartCount } = useStore()

  const [banners, setBanners] = useState<Banner[]>([])
  const [categories, setCategories] = useState<Category[]>([])
  const [featuredProducts, setFeaturedProducts] = useState<Product[]>([])
  const [dataLoading, setDataLoading] = useState(true)

  const [activeBanner, setActiveBanner] = useState(0)
  const scrollRef = useRef<HTMLDivElement>(null)

  const isKOT = store?.store_type === 'KOT'

  // Fetch home data (store is already validated by StoreProvider)
  useEffect(() => {
    if (!storeCode) return

    setDataLoading(true)
    const requests: [Promise<{ banners: Banner[]; total_count: number }>, Promise<{ categories: Category[]; total_count: number }>, Promise<{ products: Product[]; total_count: number }>] = [
      isKOT
        ? Promise.resolve({ banners: [], total_count: 0 })
        : getBanners(storeCode).catch(() => ({ banners: [], total_count: 0 })),
      getCategories(storeCode).catch(() => ({ categories: [], total_count: 0 })),
      getFeaturedProducts(storeCode).catch(() => ({ products: [], total_count: 0 })),
    ]
    Promise.all(requests).then(([bannersData, categoriesData, featuredData]) => {
      setBanners(bannersData.banners)
      setCategories(categoriesData.categories)
      setFeaturedProducts(featuredData.products)
      setDataLoading(false)
    })
  }, [storeCode, isKOT])

  // Auto-scroll banners every 5 seconds
  useEffect(() => {
    if (banners.length <= 1) return
    const timer = setInterval(() => {
      if (!scrollRef.current) return
      const el = scrollRef.current
      const slide = el.firstElementChild as HTMLElement | null
      if (!slide) return
      const slideWidth = slide.offsetWidth + 10
      const maxIndex = banners.length - 1
      const nextIndex = activeBanner >= maxIndex ? 0 : activeBanner + 1
      el.scrollTo({ left: nextIndex * slideWidth, behavior: 'smooth' })
      setActiveBanner(nextIndex)
    }, 5000)
    return () => clearInterval(timer)
  }, [activeBanner, banners.length])

  const handleBannerScroll = () => {
    if (!scrollRef.current) return
    const el = scrollRef.current
    const slide = el.firstElementChild as HTMLElement | null
    if (!slide) return
    const slideWidth = slide.offsetWidth + 10
    const index = Math.round(el.scrollLeft / slideWidth)
    setActiveBanner(Math.min(index, banners.length - 1))
  }

  const scrollToBanner = (index: number) => {
    if (!scrollRef.current) return
    const el = scrollRef.current
    const slide = el.firstElementChild as HTMLElement | null
    if (!slide) return
    const slideWidth = slide.offsetWidth + 10
    el.scrollTo({ left: index * slideWidth, behavior: 'smooth' })
    setActiveBanner(index)
  }

  // Cart actions
  const handleAddToCart = useCallback(async (productId: number) => {
    try {
      const data = await addToCart(storeCode, productId)
      if (data.is_new_session) {
        saveSession(data.session_id)
      }
      // Update local product state
      setFeaturedProducts((prev) =>
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
      setFeaturedProducts((prev) =>
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
      setFeaturedProducts((prev) =>
        prev.map((p) =>
          p.cart_id === cartId
            ? {
                ...p,
                cart_quantity: data.quantity,
                is_in_cart: !data.removed,
                cart_id: data.removed ? null : p.cart_id,
              }
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

  // Skeleton loading
  if (dataLoading) {
    return (
      <div className="bg-white min-h-screen font-poppins">
        {/* Header skeleton */}
        <header className="flex items-center justify-between px-4 pt-3 pb-3">
          <div className="flex items-center gap-[14px]">
            <div className="w-[42px] h-[42px] rounded-full bg-gray-200 animate-pulse" />
            <div>
              <div className="h-4 w-[100px] bg-gray-200 animate-pulse rounded" />
              <div className="h-3 w-[150px] bg-gray-200 animate-pulse rounded mt-1" />
            </div>
          </div>
          <div className="flex items-center gap-[10px]">
            <div className="w-[42px] h-[42px] rounded-full bg-gray-200 animate-pulse" />
            <div className="w-[42px] h-[42px] rounded-full bg-gray-200 animate-pulse" />
          </div>
        </header>

        {/* Banner skeleton */}
        {!isKOT && (
          <div className="mt-[18px] px-[24px]">
            <div className="h-[181px] bg-gray-200 animate-pulse rounded-[19px]" />
          </div>
        )}

        {/* Category skeleton */}
        <div className="mt-6 px-4">
          <div className="flex items-center justify-between mb-3">
            <div className="h-5 w-[80px] bg-gray-200 animate-pulse rounded" />
            <div className="h-4 w-[50px] bg-gray-200 animate-pulse rounded" />
          </div>
          <div className="flex gap-3">
            {[1, 2, 3, 4, 5].map((i) => (
              <div
                key={i}
                className="shrink-0 rounded-[17px] border border-[#ececec] flex flex-col items-center justify-center gap-1 py-2 px-2 w-[calc((100vw-84px)/4)] sm:w-[calc((100vw-104px)/5)]"
              >
                <div className="w-[52px] h-[52px] rounded-full bg-gray-200 animate-pulse" />
                <div className="h-3 w-[50px] bg-gray-200 animate-pulse rounded mt-1" />
              </div>
            ))}
          </div>
        </div>

        {/* Products skeleton */}
        <div className="mt-5 px-4">
          <div className="h-5 w-[120px] bg-gray-200 animate-pulse rounded mb-3" />
          <div className="grid grid-cols-2 sm:grid-cols-3 gap-4">
            {[1, 2, 3, 4, 5, 6].map((i) => (
              <div key={i}>
                <div className="rounded-[17px] border border-[#ececec] overflow-hidden">
                  <div className="w-full aspect-square bg-gray-200 animate-pulse" />
                  <div className="p-3">
                    <div className="h-4 w-3/4 bg-gray-200 animate-pulse rounded" />
                    <div className="h-3 w-1/3 bg-gray-200 animate-pulse rounded mt-2" />
                    <div className="flex items-center justify-between mt-3">
                      <div className="h-5 w-[50px] bg-gray-200 animate-pulse rounded" />
                      <div className="h-[30px] w-[80px] bg-gray-200 animate-pulse rounded-[8px]" />
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="bg-white min-h-screen font-poppins">
      {/* Header */}
      <header className="sticky top-0 z-50 bg-white flex items-center justify-between px-4 pt-3 pb-3">
        <div className="flex items-center gap-[14px]">
          <img
            src={logo}
            alt={store?.name || 'Store'}
            className="w-[42px] h-[42px] rounded-full object-cover"
          />
          <div>
            <p className="text-[16px] font-bold text-dark leading-normal">
              {store?.short_name || store?.name || 'Store'}
            </p>
            <p className="text-[10px] text-body leading-normal line-clamp-1">
              {store?.address || ''}
            </p>
          </div>
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
      </header>

      {/* Banner Carousel (QSR only) */}
      {!isKOT && banners.length > 0 && (
        <div className="mt-[18px]">
          <div
            ref={scrollRef}
            onScroll={handleBannerScroll}
            className="flex gap-[10px] overflow-x-auto snap-x snap-mandatory scrollbar-hide px-[24px]"
          >
            {banners.map((banner) => (
              <div
                key={banner.id}
                onClick={() => {
                  const type = banner.action.type.toLowerCase()
                  const payload = banner.action.payload
                  if (type === 'category' && payload) navigate(`/${storeCode}/category/${payload}`)
                  else if (type === 'product' && payload) navigate(`/${storeCode}/product/${payload}`)
                  else if (type === 'url' && payload) window.open(payload, '_blank')
                }}
                className={`snap-center shrink-0 rounded-[19px] overflow-hidden h-[181px] w-[calc(100vw-48px)] sm:w-[calc(50vw-36px)] ${
                  banner.action.type.toLowerCase() !== 'none' ? 'cursor-pointer' : ''
                }`}
              >
                <img
                  src={banner.image_url}
                  alt={banner.title || 'Promotional banner'}
                  className="w-full h-full object-cover"
                />
              </div>
            ))}
          </div>

          {banners.length > 1 && (
            <div className="flex items-center justify-center gap-[7px] mt-[13px]">
              {banners.map((_, i) => (
                <button
                  key={i}
                  onClick={() => scrollToBanner(i)}
                  className={`rounded-full transition-all duration-300 ${
                    activeBanner === i
                      ? 'w-3 h-[3px] bg-primary-red'
                      : 'w-[3px] h-[3px] bg-primary-red/30'
                  }`}
                />
              ))}
            </div>
          )}
        </div>
      )}

      {/* Category Section */}
      {categories.length > 0 && (
        <div className="mt-6 px-4">
          <SectionHeader title="Category" onViewAll={() => navigate(`/${storeCode}/categories`)} />
          <div className="flex gap-3 overflow-x-auto scrollbar-hide -mx-4 px-4 pb-1">
            {categories.map((cat) => (
              <button
                key={cat.id}
                onClick={() => navigate(`/${storeCode}/category/${cat.id}`)}
                className="shrink-0 min-h-[100px] py-2 bg-white rounded-[17px] border border-[#ececec] flex flex-col items-center justify-center gap-1 px-2 w-[calc((100vw-84px)/4)] sm:w-[calc((100vw-104px)/5)]"
              >
                <img
                  src={cat.thumbnail || cat.icon}
                  alt={cat.name}
                  className="w-[52px] h-[52px] object-contain"
                />
                <span className="text-[11px] text-dark leading-tight text-center w-full">
                  {cat.name}
                </span>
              </button>
            ))}
          </div>
        </div>
      )}

      {/* Popular Items */}
      {featuredProducts.length > 0 && (
        <div className="mt-5 px-4 pb-8">
          <SectionHeader title="Popular Items" />
          <div className="grid grid-cols-2 sm:grid-cols-3 gap-4">
            {featuredProducts.map((product) => (
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
  )
}

export default App
