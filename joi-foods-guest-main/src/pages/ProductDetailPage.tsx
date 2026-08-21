import { useState, useEffect, useCallback, useRef } from 'react'
import { useParams, useNavigate } from 'react-router-dom'

import backArrowIcon from '../assets/icons/back-arrow.svg'
import cartIcon from '../assets/icons/cart.svg'
import vegIcon from '../assets/icons/veg.svg'
import nonvegIcon from '../assets/icons/nonveg.svg'
import minusIcon from '../assets/icons/minus.svg'
import plusDarkIcon from '../assets/icons/plus-dark.svg'
import descriptionIcon from '../assets/icons/description.svg'
import ingredientsIcon from '../assets/icons/ingredients.svg'
import refundIcon from '../assets/icons/refund.svg'

import { useStore } from '../contexts/StoreContext'
import { getProductDetail } from '../api/catalog'
import { addToCart, incrementCart, decrementCart } from '../api/cart'
import type { Product } from '../types/api'

function ChevronDown({ open }: { open: boolean }) {
  return (
    <svg
      width="13"
      height="7"
      viewBox="0 0 13 7"
      fill="none"
      className={`transition-transform duration-200 ${open ? 'rotate-180' : ''}`}
    >
      <path d="M1 1L6.5 6L12 1" stroke="#888888" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  )
}

export default function ProductDetailPage() {
  const { productId } = useParams<{ productId: string }>()
  const navigate = useNavigate()
  const { storeCode, cartCount, saveSession, refreshCartCount } = useStore()

  const [product, setProduct] = useState<Product | null>(null)
  const [loading, setLoading] = useState(true)
  const [activeImage, setActiveImage] = useState(0)
  const galleryRef = useRef<HTMLDivElement>(null)
  const [openSections, setOpenSections] = useState<Record<string, boolean>>({
    description: false,
    ingredients: false,
    refund: false,
  })

  // Fetch product detail
  useEffect(() => {
    if (!storeCode || !productId) return

    setLoading(true)
    getProductDetail(storeCode, Number(productId))
      .then((data) => setProduct(data.product))
      .catch((err) => console.error('Failed to load product:', err))
      .finally(() => setLoading(false))
  }, [storeCode, productId])

  // Cart actions
  const handleAddToCart = useCallback(async () => {
    if (!product || !storeCode) return
    try {
      const data = await addToCart(storeCode, product.id)
      if (data.is_new_session) saveSession(data.session_id)
      setProduct((prev) =>
        prev ? { ...prev, is_in_cart: true, cart_id: data.cart_id, cart_quantity: data.quantity } : prev,
      )
      refreshCartCount()
    } catch (err) {
      console.error('Add to cart failed:', err)
    }
  }, [product, storeCode, saveSession, refreshCartCount])

  const handleIncrement = useCallback(async () => {
    if (!product?.cart_id) return
    try {
      const data = await incrementCart(product.cart_id)
      setProduct((prev) =>
        prev ? { ...prev, cart_quantity: data.quantity } : prev,
      )
      refreshCartCount()
    } catch (err) {
      console.error('Increment failed:', err)
    }
  }, [product?.cart_id, refreshCartCount])

  const handleDecrement = useCallback(async () => {
    if (!product?.cart_id) return
    try {
      const data = await decrementCart(product.cart_id)
      setProduct((prev) =>
        prev
          ? {
              ...prev,
              cart_quantity: data.quantity,
              is_in_cart: !data.removed,
              cart_id: data.removed ? null : prev.cart_id,
            }
          : prev,
      )
      refreshCartCount()
    } catch (err) {
      console.error('Decrement failed:', err)
    }
  }, [product?.cart_id, refreshCartCount])

  const handleOrderNow = useCallback(async () => {
    if (!product || !storeCode) return
    if (product.is_in_cart && product.cart_quantity > 0) {
      navigate(`/${storeCode}/cart`)
      return
    }
    try {
      const data = await addToCart(storeCode, product.id)
      if (data.is_new_session) saveSession(data.session_id)
      refreshCartCount()
      navigate(`/${storeCode}/cart`)
    } catch (err) {
      console.error('Add to cart failed:', err)
    }
  }, [product, storeCode, navigate, saveSession, refreshCartCount])

  const toggleSection = (key: string) => {
    setOpenSections((prev) => ({ ...prev, [key]: !prev[key] }))
  }

  const handleGalleryScroll = () => {
    if (!galleryRef.current) return
    const el = galleryRef.current
    const slideWidth = el.offsetWidth
    const index = Math.round(el.scrollLeft / slideWidth)
    setActiveImage(Math.min(index, galleryImages.length - 1))
  }

  if (loading) {
    return (
      <div className="bg-white min-h-screen font-poppins">
        <div className="w-full aspect-square bg-gray-200 animate-pulse" />
        <div className="px-4 pt-5">
          <div className="flex items-start justify-between">
            <div className="h-6 bg-gray-200 animate-pulse rounded w-2/3" />
            <div className="w-[21px] h-[21px] bg-gray-200 animate-pulse rounded" />
          </div>
          <div className="h-3 bg-gray-200 animate-pulse rounded w-1/4 mt-2" />
          <div className="border-t border-border mt-4" />
          <div className="flex items-center justify-between mt-4">
            <div className="h-8 bg-gray-200 animate-pulse rounded w-1/3" />
            <div className="h-[38px] bg-gray-200 animate-pulse rounded-[8px] w-[130px]" />
          </div>
          <div className="mt-6 flex flex-col gap-[14px]">
            {[1, 2, 3].map((i) => (
              <div key={i}>
                <div className="flex items-center gap-3">
                  <div className="w-6 h-6 bg-gray-200 animate-pulse rounded" />
                  <div className="h-4 bg-gray-200 animate-pulse rounded w-1/3" />
                </div>
                {i < 3 && <div className="border-t border-border mt-[14px]" />}
              </div>
            ))}
          </div>
          <div className="mt-8">
            <div className="h-[50px] bg-gray-200 animate-pulse rounded-[56px] w-full" />
          </div>
        </div>
      </div>
    )
  }

  if (!product) {
    return (
      <div className="bg-white min-h-screen font-poppins flex items-center justify-center">
        <p className="text-muted text-[14px]">Product not found</p>
      </div>
    )
  }

  const galleryImages = product.images.length > 0 ? product.images : [product.thumbnail]
  const inCart = product.is_in_cart && product.cart_quantity > 0
  const totalPrice = product.price * product.cart_quantity
  const unitLabel = product.cart_quantity > 1 ? `(₹${product.price} X ${product.cart_quantity})` : ''

  return (
    <div className="bg-white min-h-screen font-poppins">
      {/* Hero Image Gallery */}
      <div className="relative w-full aspect-square">
        <div
          ref={galleryRef}
          onScroll={handleGalleryScroll}
          className="flex overflow-x-auto snap-x snap-mandatory scrollbar-hide w-full h-full"
        >
          {galleryImages.map((img, i) => (
            <div key={i} className="snap-center shrink-0 w-full h-full">
              <img
                src={img}
                alt={`${product.name} ${i + 1}`}
                className="w-full h-full object-cover"
              />
            </div>
          ))}
        </div>

        {/* Back Button */}
        <button
          onClick={() => navigate(-1)}
          className="absolute top-3 left-4 w-[38px] h-[38px] rounded-full bg-white/80 flex items-center justify-center"
        >
          <img src={backArrowIcon} alt="Back" className="w-5 h-5" />
        </button>

        {/* Cart Button */}
        <div className="absolute top-3 right-4">
          <button
            onClick={() => navigate(`/${storeCode}/cart`)}
            className="w-[38px] h-[38px] rounded-full bg-white/80 flex items-center justify-center relative"
          >
            <img src={cartIcon} alt="Cart" className="w-6 h-6" />
            {cartCount > 0 && (
              <span className="absolute -top-1 -right-1 min-w-[18px] h-[18px] rounded-full bg-orange text-white text-[10px] font-bold flex items-center justify-center px-1">
                {cartCount}
              </span>
            )}
          </button>
        </div>

        {/* Image Dots */}
        <div className="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-[6px]">
            {galleryImages.map((_, i) => (
              <span
                key={i}
                className={`w-[8px] h-[8px] rounded-full ${
                  activeImage === i ? 'bg-white' : 'bg-white/50'
                }`}
              />
            ))}
        </div>
      </div>

      {/* Product Info */}
      <div className="px-4 pt-5">
        {/* Name + Veg Badge */}
        <div className="flex items-start justify-between">
          <p className="text-[22px] font-medium text-body leading-normal">{product.name}</p>
          <img
            src={product.is_vegetarian ? vegIcon : nonvegIcon}
            alt={product.is_vegetarian ? 'Veg' : 'Non-veg'}
            className="w-[21px] h-[21px] mt-1"
          />
        </div>

        {/* Stock Status */}
        <p
          className={`text-[12px] font-semibold leading-4 mt-1 ${
            product.is_in_stock ? 'text-green' : 'text-red-stock'
          }`}
        >
          {product.is_in_stock ? 'In Stock' : 'Out of stock'}
        </p>

        {/* Divider */}
        <div className="border-t border-border mt-4" />

        {/* Price + Stepper / Add To Cart */}
        <div className="flex items-center justify-between mt-4">
          <div className="flex items-baseline gap-2">
            <p className="text-[26px] font-semibold text-dark leading-[42px]">
              ₹{(inCart ? totalPrice : product.price).toFixed(2)}
            </p>
            {inCart && unitLabel && (
              <p className="text-[12px] font-medium text-[#8f8f8f] leading-4">{unitLabel}</p>
            )}
          </div>

          {product.is_in_stock && !inCart && (
            <button
              onClick={handleAddToCart}
              className="flex items-center gap-2 h-[38px] px-4 rounded-[8px] border border-border"
            >
              <img src={cartIcon} alt="" className="w-5 h-5" />
              <span className="text-[13px] font-medium text-dark">Add To Cart</span>
            </button>
          )}

          {product.is_in_stock && inCart && (
            <div className="flex items-center gap-4">
              <button
                onClick={handleDecrement}
                className="w-[30px] h-[30px] rounded-[6px] border border-muted flex items-center justify-center"
              >
                <img src={minusIcon} alt="Remove" className="w-[10px] h-[2px]" />
              </button>
              <span className="text-[14px] font-semibold text-dark text-center min-w-[8px]">
                {product.cart_quantity}
              </span>
              <button
                onClick={handleIncrement}
                className="w-[30px] h-[30px] rounded-[6px] bg-orange flex items-center justify-center"
              >
                <img src={plusDarkIcon} alt="Add" className="w-[11px] h-[11px]" />
              </button>
            </div>
          )}
        </div>

        {/* Accordion Sections */}
        <div className="mt-6 flex flex-col gap-[14px]">
          {/* Description */}
          {product.description && (
            <>
              <div>
                <button
                  onClick={() => toggleSection('description')}
                  className="flex items-center justify-between w-full"
                >
                  <div className="flex items-center gap-3">
                    <img src={descriptionIcon} alt="" className="w-6 h-6" />
                    <span className="text-[14px] font-medium text-dark">Description</span>
                  </div>
                  <ChevronDown open={openSections.description} />
                </button>
                {openSections.description && (
                  <p className="text-[14px] text-body leading-6 mt-3">
                    {product.description}
                  </p>
                )}
              </div>
              <div className="border-t border-border" />
            </>
          )}

          {/* Ingredients */}
          {product.ingredients && (
            <>
              <div>
                <button
                  onClick={() => toggleSection('ingredients')}
                  className="flex items-center justify-between w-full"
                >
                  <div className="flex items-center gap-3">
                    <img src={ingredientsIcon} alt="" className="w-6 h-6" />
                    <span className="text-[14px] font-medium text-dark">Ingredients</span>
                  </div>
                  <ChevronDown open={openSections.ingredients} />
                </button>
                {openSections.ingredients && (
                  <p className="text-[14px] text-body leading-6 mt-3">
                    {product.ingredients}
                  </p>
                )}
              </div>
              <div className="border-t border-border" />
            </>
          )}

          {/* Refund / Cancel */}
          <div>
            <button
              onClick={() => toggleSection('refund')}
              className="flex items-center justify-between w-full"
            >
              <div className="flex items-center gap-3">
                <img src={refundIcon} alt="" className="w-6 h-6" />
                <span className="text-[14px] font-medium text-dark">Refund / Cancel</span>
              </div>
              <ChevronDown open={openSections.refund} />
            </button>
            {openSections.refund && (
              <p className="text-[14px] text-body leading-6 mt-3">
                Items cannot be refunded or cancelled once the order is confirmed.
              </p>
            )}
          </div>
        </div>

        {/* Order Now Button */}
        <div className="mt-8 pb-8">
          <button
            onClick={handleOrderNow}
            className={`w-full h-[50px] rounded-[56px] bg-primary-red text-white text-[14px] font-semibold ${
              !product.is_in_stock ? 'opacity-50 cursor-not-allowed' : ''
            }`}
            disabled={!product.is_in_stock}
          >
            Order Now
          </button>
        </div>
      </div>
    </div>
  )
}
