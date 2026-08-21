import { useState, useEffect, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import Select, { type StylesConfig } from 'react-select'
import { toast } from 'sonner'

import backArrowIcon from '../assets/icons/back-arrow.svg'
import vegIcon from '../assets/icons/veg.svg'
import nonvegIcon from '../assets/icons/nonveg.svg'
import minusIcon from '../assets/icons/minus.svg'
import plusDarkIcon from '../assets/icons/plus-dark.svg'

import { useStore } from '../contexts/StoreContext'
import { getCartItems, incrementCart, decrementCart } from '../api/cart'
import { getDeliveryLocations, initiateOrder, completeOrder } from '../api/orders'
import { ApiError } from '../api/client'
import type { CartItem, CartSummary, DeliveryLocation } from '../types/api'

interface LocationOption {
  value: number
  label: string
  detail: string
}

const locationSelectStyles: StylesConfig<LocationOption, false> = {
  control: (base, state) => ({
    ...base,
    minHeight: 48,
    borderRadius: 10,
    borderColor: state.isFocused ? '#bd3839' : '#e0e0e0',
    boxShadow: state.isFocused ? '0 0 0 1px #bd3839' : 'none',
    fontFamily: "'Poppins', sans-serif",
    fontSize: 14,
    paddingLeft: 4,
    '&:hover': { borderColor: state.isFocused ? '#bd3839' : '#ccc' },
  }),
  option: (base, state) => ({
    ...base,
    fontFamily: "'Poppins', sans-serif",
    fontSize: 14,
    backgroundColor: state.isSelected ? '#fff8f8' : state.isFocused ? '#f9f9f9' : 'white',
    color: '#1a1a1a',
    padding: '10px 16px',
    '&:active': { backgroundColor: '#fff0f0' },
  }),
  placeholder: (base) => ({
    ...base,
    color: '#8f8f8f',
    fontSize: 14,
  }),
  singleValue: (base) => ({
    ...base,
    color: '#1a1a1a',
    fontSize: 14,
  }),
  menu: (base) => ({
    ...base,
    borderRadius: 10,
    overflow: 'hidden',
    boxShadow: '0 4px 20px rgba(0,0,0,0.1)',
    zIndex: 60,
  }),
  menuList: (base) => ({
    ...base,
    padding: 0,
  }),
  indicatorSeparator: () => ({ display: 'none' }),
  dropdownIndicator: (base, state) => ({
    ...base,
    color: '#888',
    transition: 'transform 200ms',
    transform: state.selectProps.menuIsOpen ? 'rotate(180deg)' : 'rotate(0deg)',
    '&:hover': { color: '#888' },
  }),
}

function loadRazorpay(): Promise<boolean> {
  return new Promise((resolve) => {
    if (document.getElementById('razorpay-script')) {
      resolve(true)
      return
    }
    const script = document.createElement('script')
    script.id = 'razorpay-script'
    script.src = 'https://checkout.razorpay.com/v1/checkout.js'
    script.onload = () => resolve(true)
    script.onerror = () => resolve(false)
    document.body.appendChild(script)
  })
}

export default function OrderSummaryPage() {
  const navigate = useNavigate()
  const { storeCode, store, refreshCartCount } = useStore()

  const [items, setItems] = useState<CartItem[]>([])
  const [summary, setSummary] = useState<CartSummary | null>(null)
  const [loading, setLoading] = useState(true)
  const [placing, setPlacing] = useState(false)
  const [guestName, setGuestName] = useState('')
  const [guestPhone, setGuestPhone] = useState('')
  const [deliveryLocations, setDeliveryLocations] = useState<DeliveryLocation[]>([])
  const [selectedLocationId, setSelectedLocationId] = useState<number | undefined>(undefined)

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

  // Fetch delivery locations
  useEffect(() => {
    if (!storeCode) return
    getDeliveryLocations(storeCode)
      .then((data) => setDeliveryLocations(data.locations))
      .catch(() => setDeliveryLocations([]))
  }, [storeCode])

  const handleIncrement = useCallback(async (cartId: number) => {
    try {
      await incrementCart(cartId)
      refreshCartCount()
      fetchCart()
    } catch (err) {
      console.error('Increment failed:', err)
    }
  }, [refreshCartCount, fetchCart])

  const handleDecrement = useCallback(async (cartId: number) => {
    try {
      await decrementCart(cartId)
      refreshCartCount()
      fetchCart()
    } catch (err) {
      console.error('Decrement failed:', err)
    }
  }, [refreshCartCount, fetchCart])

  const handlePlaceOrder = async () => {
    if (!storeCode || !guestName.trim() || !guestPhone.trim()) return

    const phone = guestPhone.trim().replace(/\s+/g, '')
    if (!/^\d{10,}$/.test(phone)) {
      toast.error('Please enter a valid phone number (minimum 10 digits)')
      return
    }
    if (guestName.trim().length < 2) {
      toast.error('Please enter a valid name')
      return
    }

    setPlacing(true)

    try {
      // Step 1: Initiate order
      const data = await initiateOrder(storeCode, guestName.trim(), guestPhone.trim(), selectedLocationId)

      // Step 2: Load Razorpay
      const loaded = await loadRazorpay()
      if (!loaded) {
        toast.error('Payment gateway failed to load. Please try again.')
        setPlacing(false)
        return
      }

      // Step 3: Open Razorpay checkout
      const options = {
        key: data.razorpay_key,
        amount: data.amount,
        currency: data.currency,
        name: store?.name || 'JOI Foods',
        description: `Order #${data.pending_order_id}`,
        order_id: data.razorpay_order_id,
        prefill: {
          name: data.guest_name,
          contact: data.guest_phone,
        },
        handler: async (response: { razorpay_order_id: string; razorpay_payment_id: string; razorpay_signature: string }) => {
          try {
            // Step 4: Complete order
            const result = await completeOrder(
              storeCode,
              response.razorpay_order_id,
              response.razorpay_payment_id,
              response.razorpay_signature,
            )
            refreshCartCount()
            // Navigate to success page with order data
            navigate(`/${storeCode}/order-success`, { state: { orderData: result } })
          } catch (err) {
            console.error('Complete order failed:', err)
            toast.error(err instanceof ApiError ? err.message : 'Order completion failed. Please contact support.')
          } finally {
            setPlacing(false)
          }
        },
        modal: {
          ondismiss: () => setPlacing(false),
        },
        theme: { color: '#bd3839' },
      }

      const rzp = new (window as any).Razorpay(options)
      rzp.open()
    } catch (err) {
      console.error('Initiate order failed:', err)
      toast.error(err instanceof ApiError ? err.message : 'Failed to initiate order. Please try again.')
      setPlacing(false)
    }
  }

  const requiresLocation = deliveryLocations.length > 0
  const canPlaceOrder = guestName.trim() && guestPhone.trim() && items.length > 0 && !placing && (!requiresLocation || selectedLocationId)

  return (
    <div className="bg-white min-h-screen font-poppins pb-[180px]">
      {/* Header */}
      <header className="sticky top-0 z-50 bg-white border-b border-[#e7e7e7] px-4 pt-3 pb-3">
        <div className="flex items-center justify-center relative">
          <button
            onClick={() => navigate(-1)}
            className="absolute left-0 w-8 h-8 rounded-full border border-border flex items-center justify-center"
          >
            <img src={backArrowIcon} alt="Back" className="w-5 h-5" />
          </button>
          <h1 className="text-[20px] font-semibold text-dark leading-normal">
            Order Summary
          </h1>
        </div>
      </header>

      {loading ? (
        <div className="flex items-center justify-center py-12">
          <p className="text-muted text-[14px]">Loading...</p>
        </div>
      ) : (
        <div className="px-4">
          {/* Items Section */}
          <p className="text-[18px] font-semibold text-dark mt-4">Items</p>
          <div className="mt-2">
            {items.map((item, i) => (
              <div key={item.cart_id}>
                <div className="flex items-center gap-3 py-3">
                  {/* Thumbnail */}
                  <div className="relative w-[80px] h-[80px] shrink-0 rounded-[12px] overflow-hidden">
                    <img src={item.thumbnail} alt={item.product_name} className="w-full h-full object-cover" />
                    <img
                      src={item.is_vegetarian ? vegIcon : nonvegIcon}
                      alt={item.is_vegetarian ? 'Veg' : 'Non-veg'}
                      className="absolute top-1 right-1 w-[18px] h-[18px]"
                    />
                  </div>
                  {/* Info */}
                  <div className="flex-1 min-w-0">
                    <p className="text-[14px] font-medium text-body leading-4">{item.product_name}</p>
                    <p className={`text-[10px] font-medium leading-4 mt-1 ${item.is_in_stock ? 'text-green' : 'text-red-stock'}`}>
                      {item.is_in_stock ? 'In Stock' : 'Out of stock'}
                    </p>
                    <div className="flex items-center gap-1 mt-2">
                      <p className="text-[16px] font-semibold text-dark leading-4">₹{item.total.toFixed(2)}</p>
                      {item.quantity > 1 && (
                        <p className="text-[12px] font-medium text-[#8f8f8f] leading-4">({item.unit_price}X {item.quantity})</p>
                      )}
                    </div>
                  </div>
                  {/* Stepper */}
                  <div className="flex items-center gap-4 shrink-0">
                    <button
                      onClick={() => handleDecrement(item.cart_id)}
                      className="w-[30px] h-[30px] rounded-[6px] border border-muted flex items-center justify-center"
                    >
                      <img src={minusIcon} alt="Remove" className="w-[10px] h-[2px]" />
                    </button>
                    <span className="text-[14px] font-semibold text-dark text-center min-w-[8px]">{item.quantity}</span>
                    <button
                      onClick={() => handleIncrement(item.cart_id)}
                      className="w-[30px] h-[30px] rounded-[6px] bg-orange flex items-center justify-center"
                    >
                      <img src={plusDarkIcon} alt="Add" className="w-[11px] h-[11px]" />
                    </button>
                  </div>
                </div>
                {i < items.length - 1 && <div className="border-t border-border" />}
              </div>
            ))}
          </div>

          {/* Total amount (items) */}
          {summary && (
            <>
              <div className="border-t border-dashed border-[#e0e0e0] mt-2" />
              <div className="flex items-center justify-between py-3">
                <p className="text-[14px] text-dark">Total amount</p>
                <p className="text-[14px] font-bold text-dark">₹{summary.total_amount.toFixed(2)}</p>
              </div>
            </>
          )}

          {/* Outlet Address */}
          <div className="border-t border-dashed border-[#e0e0e0]" />
          <div className="py-4">
            <p className="text-[18px] font-semibold text-dark">Outlet address</p>
            <p className="text-[14px] text-muted leading-[20px] mt-1">{store?.address || ''}</p>
          </div>

          {/* Bill Info */}
          <div className="border-t border-dashed border-[#e0e0e0]" />
          {summary && (
            <div className="py-4">
              <p className="text-[18px] font-semibold text-dark">Bill info</p>
              <div className="flex items-center justify-between mt-3">
                <p className="text-[14px] text-body leading-[20px]">Price</p>
                <p className="text-[14px] text-dark">₹{summary.formatted.subtotal}</p>
              </div>
              <div className="flex items-center justify-between mt-2">
                <p className="text-[14px] text-body leading-[20px]">GST</p>
                <p className="text-[14px] text-dark">₹{summary.formatted.tax_amount}</p>
              </div>
              <div className="border-t border-dashed border-[#e0e0e0] mt-3" />
              <div className="flex items-center justify-between mt-3">
                <p className="text-[14px] font-semibold text-dark">Total amount</p>
                <p className="text-[14px] font-bold text-dark">₹{summary.formatted.total_amount}</p>
              </div>
              <div className="border-t border-dashed border-[#e0e0e0] mt-3" />
              <div className="flex items-center justify-between mt-3">
                <p className="text-[16px] font-semibold text-dark">Balance amount to pay</p>
                <p className="text-[16px] font-bold text-dark">{summary.formatted.amount_payable}</p>
              </div>
            </div>
          )}

          {/* Delivery Location */}
          {deliveryLocations.length > 0 && (
            <>
              <div className="border-t border-dashed border-[#e0e0e0]" />
              <div className="py-4">
                <p className="text-[18px] font-semibold text-dark">Delivery location</p>
                <div className="mt-3">
                  <Select<LocationOption>
                    options={deliveryLocations.map((loc) => ({
                      value: loc.id,
                      label: loc.name,
                      detail: [loc.floor, loc.building].filter(Boolean).join(', '),
                    }))}
                    value={
                      selectedLocationId
                        ? deliveryLocations
                            .filter((l) => l.id === selectedLocationId)
                            .map((l) => ({
                              value: l.id,
                              label: l.name,
                              detail: [l.floor, l.building].filter(Boolean).join(', '),
                            }))[0]
                        : null
                    }
                    onChange={(opt) => setSelectedLocationId(opt?.value)}
                    placeholder="Select delivery location"
                    isSearchable={false}
                    styles={locationSelectStyles}
                    formatOptionLabel={(opt) => (
                      <div>
                        <p className="text-[14px] text-dark">{opt.label}</p>
                        {opt.detail && (
                          <p className="text-[11px] text-muted mt-0.5">{opt.detail}</p>
                        )}
                      </div>
                    )}
                  />
                </div>
              </div>
            </>
          )}

          {/* Guest Details */}
          <div className="border-t border-dashed border-[#e0e0e0]" />
          <div className="py-4">
            <p className="text-[18px] font-semibold text-dark">Your details</p>
            <input
              type="text"
              placeholder="Your name"
              value={guestName}
              onChange={(e) => setGuestName(e.target.value)}
              className="w-full mt-3 h-[48px] border border-[#e0e0e0] rounded-[10px] px-4 text-[14px] text-dark placeholder-[#8f8f8f] outline-none"
            />
            <input
              type="tel"
              inputMode="numeric"
              placeholder="Phone number"
              maxLength={10}
              value={guestPhone}
              onChange={(e) => {
                const digits = e.target.value.replace(/[^\d]/g, '')
                if (digits.length <= 10) setGuestPhone(digits)
              }}
              className="w-full mt-3 h-[48px] border border-[#e0e0e0] rounded-[10px] px-4 text-[14px] text-dark placeholder-[#8f8f8f] outline-none"
            />
          </div>
        </div>
      )}

      {/* Bottom Actions */}
      {!loading && items.length > 0 && (
        <div className="fixed bottom-0 left-0 right-0 z-50 bg-white px-5 pt-4 pb-6">
          <button
            onClick={handlePlaceOrder}
            disabled={!canPlaceOrder}
            className={`w-full h-[50px] rounded-[64px] bg-primary-red text-white text-[14px] font-bold ${
              !canPlaceOrder ? 'opacity-50' : ''
            }`}
          >
            {placing ? 'Processing...' : 'Place Order'}
          </button>
          <button
            onClick={() => navigate(-1)}
            className="w-full mt-3 text-[14px] font-semibold text-dark underline text-center"
          >
            Cancel
          </button>
        </div>
      )}
    </div>
  )
}
