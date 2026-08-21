import { useState, useEffect, useCallback, useRef } from 'react'
import { useNavigate } from 'react-router-dom'

import backArrowIcon from '../assets/icons/back-arrow.svg'
import emptyCartImg from '../assets/images/food-bill.svg'
import vegIcon from '../assets/icons/veg.svg'
import nonvegIcon from '../assets/icons/nonveg.svg'
import minusIcon from '../assets/icons/minus.svg'
import plusDarkIcon from '../assets/icons/plus-dark.svg'

import { useStore } from '../contexts/StoreContext'
import { getCartItems, incrementCart, decrementCart, removeFromCart } from '../api/cart'
import type { CartItem, CartSummary } from '../types/api'

function SwipeableCartItem({
  item,
  onIncrement,
  onDecrement,
  onRemove,
}: {
  item: CartItem
  onIncrement: (cartId: number) => void
  onDecrement: (cartId: number) => void
  onRemove: (cartId: number) => void
}) {
  const [offsetX, setOffsetX] = useState(0)
  const startX = useRef(0)
  const swiping = useRef(false)

  const handleTouchStart = (e: React.TouchEvent) => {
    startX.current = e.touches[0].clientX
    swiping.current = true
  }

  const handleTouchMove = (e: React.TouchEvent) => {
    if (!swiping.current) return
    const diff = startX.current - e.touches[0].clientX
    // Only allow swiping left (positive diff)
    setOffsetX(Math.max(0, Math.min(diff, 96)))
  }

  const handleTouchEnd = () => {
    swiping.current = false
    // Snap: if swiped more than 48px, reveal delete; otherwise snap back
    setOffsetX((prev) => (prev > 48 ? 96 : 0))
  }

  const unitLabel = item.quantity > 1 ? `(${item.unit_price}X ${item.quantity})` : ''

  return (
    <div className="relative overflow-hidden">
      {/* Delete background */}
      <div className="absolute right-0 top-0 bottom-0 w-[96px] bg-[#f7f7f7] flex items-center justify-center">
        <button onClick={() => onRemove(item.cart_id)}>
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M3 6H5H21" stroke="#BD3839" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
            <path d="M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6H19Z" stroke="#BD3839" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
            <path d="M10 11V17" stroke="#BD3839" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
            <path d="M14 11V17" stroke="#BD3839" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        </button>
      </div>

      {/* Card content */}
      <div
        className="relative bg-white flex items-center gap-3 py-4 transition-transform duration-200"
        style={{ transform: `translateX(-${offsetX}px)` }}
        onTouchStart={handleTouchStart}
        onTouchMove={handleTouchMove}
        onTouchEnd={handleTouchEnd}
      >
        {/* Thumbnail */}
        <div className="relative w-[80px] h-[80px] shrink-0 rounded-[12px] overflow-hidden">
          <img
            src={item.thumbnail}
            alt={item.product_name}
            className="w-full h-full object-cover"
          />
          <img
            src={item.is_vegetarian ? vegIcon : nonvegIcon}
            alt={item.is_vegetarian ? 'Veg' : 'Non-veg'}
            className="absolute top-1 right-1 w-[18px] h-[18px]"
          />
        </div>

        {/* Info */}
        <div className="flex-1 min-w-0">
          <p className="text-[14px] font-medium text-body leading-4">{item.product_name}</p>
          <p
            className={`text-[10px] font-medium leading-4 mt-[4px] ${
              item.is_in_stock ? 'text-green' : 'text-red-stock'
            }`}
          >
            {item.is_in_stock ? 'In Stock' : 'Out of stock'}
          </p>
          <div className="flex items-center gap-1 mt-[8px]">
            <p className="text-[16px] font-semibold text-dark leading-4">
              ₹{item.total.toFixed(2)}
            </p>
            {unitLabel && (
              <p className="text-[12px] font-medium text-[#8f8f8f] leading-4">{unitLabel}</p>
            )}
          </div>
        </div>

        {/* Stepper */}
        <div className="flex items-center gap-4 shrink-0">
          <button
            onClick={() => onDecrement(item.cart_id)}
            className="w-[30px] h-[30px] rounded-[6px] border border-muted flex items-center justify-center"
          >
            <img src={minusIcon} alt="Remove" className="w-[10px] h-[2px]" />
          </button>
          <span className="text-[14px] font-semibold text-dark text-center min-w-[8px]">
            {item.quantity}
          </span>
          <button
            onClick={() => onIncrement(item.cart_id)}
            className="w-[30px] h-[30px] rounded-[6px] bg-orange flex items-center justify-center"
          >
            <img src={plusDarkIcon} alt="Add" className="w-[11px] h-[11px]" />
          </button>
        </div>
      </div>
    </div>
  )
}

export default function CartPage() {
  const navigate = useNavigate()
  const { storeCode, refreshCartCount } = useStore()

  const [items, setItems] = useState<CartItem[]>([])
  const [summary, setSummary] = useState<CartSummary | null>(null)
  const [loading, setLoading] = useState(true)

  const fetchCart = useCallback(async () => {
    if (!storeCode) return
    try {
      const data = await getCartItems(storeCode)
      setItems(data.items)
      setSummary(data.summary)
    } catch (err) {
      console.error('Failed to load cart:', err)
    } finally {
      setLoading(false)
    }
  }, [storeCode])

  useEffect(() => {
    fetchCart()
  }, [fetchCart])

  const handleIncrement = useCallback(async (cartId: number) => {
    try {
      const data = await incrementCart(cartId)
      setItems((prev) =>
        prev.map((item) =>
          item.cart_id === cartId
            ? { ...item, quantity: data.quantity, total: item.unit_price * data.quantity }
            : item,
        ),
      )
      refreshCartCount()
      fetchCart()
    } catch (err) {
      console.error('Increment failed:', err)
    }
  }, [refreshCartCount, fetchCart])

  const handleDecrement = useCallback(async (cartId: number) => {
    try {
      const data = await decrementCart(cartId)
      if (data.removed) {
        setItems((prev) => prev.filter((item) => item.cart_id !== cartId))
      } else {
        setItems((prev) =>
          prev.map((item) =>
            item.cart_id === cartId
              ? { ...item, quantity: data.quantity, total: item.unit_price * data.quantity }
              : item,
          ),
        )
      }
      refreshCartCount()
      fetchCart()
    } catch (err) {
      console.error('Decrement failed:', err)
    }
  }, [refreshCartCount, fetchCart])

  const handleRemove = useCallback(async (cartId: number) => {
    try {
      await removeFromCart(cartId)
      setItems((prev) => prev.filter((item) => item.cart_id !== cartId))
      refreshCartCount()
      fetchCart()
    } catch (err) {
      console.error('Remove failed:', err)
    }
  }, [refreshCartCount, fetchCart])

  return (
    <div className="bg-white min-h-screen font-poppins pb-[120px]">
      {/* Header */}
      <header className="sticky top-0 z-50 bg-white border-b border-[#e7e7e7] px-4 pt-3 pb-3">
        <div className="flex items-center justify-center relative">
          <button
            onClick={() => navigate(`/${storeCode}`)}
            className="absolute left-0 w-8 h-8 rounded-full border border-border flex items-center justify-center"
          >
            <img src={backArrowIcon} alt="Back" className="w-5 h-5" />
          </button>
          <h1 className="text-[20px] font-semibold text-dark leading-normal">
            My Cart
          </h1>
        </div>
      </header>

      {/* Cart Items */}
      <div className="px-4">
        {loading ? (
          <div className="flex items-center justify-center py-12">
            <p className="text-muted text-[14px]">Loading cart...</p>
          </div>
        ) : items.length === 0 ? (
          <div className="flex flex-col items-center justify-center pt-24">
            <img src={emptyCartImg} alt="Empty cart" className="w-[180px] h-[180px] object-contain" />
            <p className="text-[18px] font-semibold text-dark mt-4">Your cart is empty</p>
            <p className="text-[14px] text-muted mt-1">Add items to get started</p>
            <button
              onClick={() => navigate(`/${storeCode}`)}
              className="mt-6 h-[44px] px-8 rounded-[54px] bg-primary-red text-white text-[14px] font-semibold"
            >
              Browse Menu
            </button>
          </div>
        ) : (
          <div className="divide-y divide-border">
            {items.map((item) => (
              <SwipeableCartItem
                key={item.cart_id}
                item={item}
                onIncrement={handleIncrement}
                onDecrement={handleDecrement}
                onRemove={handleRemove}
              />
            ))}
          </div>
        )}
      </div>

      {/* Bottom Bar */}
      {summary && items.length > 0 && (
        <div className="fixed bottom-0 left-0 right-0 z-50 px-4 pb-4">
          <div className="bg-white rounded-[16px] shadow-[0px_0px_20px_0px_rgba(134,134,134,0.15)] px-4 py-4 flex items-center justify-between">
            <div>
              <p className="text-[12px] text-body leading-normal">
                Total Items : {summary.total_items}
              </p>
              <div className="flex items-baseline gap-1">
                <p className="text-[20px] font-bold text-dark leading-[20px] mt-1">
                  ₹{summary.formatted.amount_payable}
                </p>
                <p className="text-[12px] text-body leading-[20px]">/Subtotal</p>
              </div>
            </div>
            <button
              onClick={() => navigate(`/${storeCode}/order-summary`)}
              className="h-[40px] px-4 rounded-[54px] bg-primary-red text-white text-[14px] font-semibold"
            >
              Order now
            </button>
          </div>
        </div>
      )}
    </div>
  )
}
