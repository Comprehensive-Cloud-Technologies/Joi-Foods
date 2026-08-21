import { useState, useEffect, Fragment } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
// @ts-expect-error: runtime named export exists but types only declare default
import { QRCode } from 'react-qr-code'

import backArrowIcon from '../assets/icons/back-arrow.svg'
import vegIcon from '../assets/icons/veg.svg'
import nonvegIcon from '../assets/icons/nonveg.svg'

import { useStore } from '../contexts/StoreContext'
import { getOrderDetails } from '../api/orders'
import type { OrderDetailsData, OrderStatus } from '../types/api'

const NEGATIVE_STATUSES = ['cancelled', 'rejected']

function isNegativeStatus(code: string) {
  return NEGATIVE_STATUSES.includes(code.toLowerCase())
}

function StatusIcon({ status }: { status: OrderStatus }) {
  const negative = isNegativeStatus(status.code)

  if (status.is_completed) {
    return (
      <div className="w-[24px] h-[24px] rounded-full bg-green flex items-center justify-center">
        <svg width="12" height="9" viewBox="0 0 12 9" fill="none">
          <path d="M1 4.5L4.5 7.5L11 1" stroke="white" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
      </div>
    )
  }

  if (status.is_current) {
    return (
      <div className="w-[24px] h-[24px] rounded-full bg-orange flex items-center justify-center">
        <svg width="12" height="9" viewBox="0 0 12 9" fill="none">
          <path d="M1 4.5L4.5 7.5L11 1" stroke="white" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
      </div>
    )
  }

  if (negative) {
    return (
      <div className="w-[24px] h-[24px] rounded-full border-[2px] border-primary-red flex items-center justify-center">
        <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
          <path d="M2 2L8 8M8 2L2 8" stroke="#bd3839" strokeWidth="1.8" strokeLinecap="round" />
        </svg>
      </div>
    )
  }

  return (
    <div className="w-[24px] h-[24px] rounded-full border-[2px] border-[#b5b5b5] flex items-center justify-center">
      <svg width="12" height="9" viewBox="0 0 12 9" fill="none">
        <path d="M1 4.5L4.5 7.5L11 1" stroke="#b5b5b5" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    </div>
  )
}

function StatusTracker({ statuses }: { statuses: OrderStatus[] }) {
  return (
    <div className="bg-[#fafafa] px-5 py-5">
      <div className="flex items-start">
        {statuses.map((status, i) => {
          const isLast = i === statuses.length - 1
          const nextStatus = !isLast ? statuses[i + 1] : null
          const nextIsNegative = nextStatus ? isNegativeStatus(nextStatus.code) : false

          let lineColor = 'border-[#d9d9d9]'
          if (status.is_completed && nextStatus?.is_completed) {
            lineColor = 'border-green'
          } else if (status.is_completed && nextStatus?.is_current) {
            lineColor = 'border-orange'
          } else if (nextIsNegative) {
            lineColor = 'border-primary-red'
          }

          return (
            <Fragment key={status.code}>
              {/* Circle + Label */}
              <div className="flex flex-col items-center shrink-0">
                <StatusIcon status={status} />
                <p className="text-[10px] text-dark text-center mt-[8px] whitespace-nowrap">{status.text}</p>
              </div>

              {/* Connecting line — flex-1 stretches to fill remaining space */}
              {!isLast && (
                <div className="flex-1 mt-[11px] mx-[6px]">
                  <div className={`h-0 w-full border-t-[1.5px] border-dashed ${lineColor}`} />
                </div>
              )}
            </Fragment>
          )
        })}
      </div>
    </div>
  )
}

function QrBottomSheet({
  qrData,
  pickupCode,
  onClose,
}: {
  qrData: string
  pickupCode: string
  onClose: () => void
}) {
  const [visible, setVisible] = useState(false)

  useEffect(() => {
    requestAnimationFrame(() => setVisible(true))
  }, [])

  const handleClose = () => {
    setVisible(false)
    setTimeout(onClose, 300)
  }

  return (
    <div className="fixed inset-0 z-[100]">
      <div
        className={`absolute inset-0 bg-[#2d2b2b] transition-opacity duration-300 ${visible ? 'opacity-72' : 'opacity-0'}`}
        onClick={handleClose}
      />
      <div className={`absolute bottom-0 left-0 right-0 bg-white rounded-t-[20px] px-5 pt-3 pb-8 transition-transform duration-300 ease-out ${visible ? 'translate-y-0' : 'translate-y-full'}`}>
        <div className="flex justify-center mb-4">
          <div className="w-[35px] h-[4px] rounded-full bg-[#d9d9d9]" />
        </div>
        <h2 className="text-[22px] font-bold text-dark text-center">Pickup QR</h2>
        <p className="text-[14px] text-body text-center mt-1">Show this to pickup item(s).</p>
        <div className="flex justify-center mt-5">
          <div className="bg-[#fff8f8] rounded-[4px] p-4">
            <QRCode value={qrData} size={200} />
          </div>
        </div>
        <div className="text-center mt-5">
          <p className="text-[14px] text-body">Your pickup code is</p>
          <p className="text-[18px] font-semibold text-body">{pickupCode}</p>
        </div>
        <button
          onClick={handleClose}
          className="w-full h-[50px] rounded-[64px] bg-primary-red text-white text-[14px] font-bold mt-6"
        >
          Close
        </button>
      </div>
    </div>
  )
}

function Skeleton() {
  return (
    <div className="bg-white min-h-screen font-poppins">
      {/* Header */}
      <header className="sticky top-0 z-50 bg-white border-b border-[#e7e7e7] px-4 pt-3 pb-3">
        <div className="flex items-center justify-center relative">
          <div className="absolute left-0 w-8 h-8 rounded-full bg-gray-200 animate-pulse" />
          <div className="h-6 w-[140px] bg-gray-200 animate-pulse rounded" />
        </div>
      </header>

      {/* Status tracker skeleton */}
      <div className="bg-[#fafafa] px-6 py-5">
        <div className="flex items-start justify-between">
          {[1, 2, 3, 4].map((i) => (
            <div key={i} className="flex items-start flex-1">
              <div className="flex flex-col items-center">
                <div className="w-[22px] h-[22px] rounded-full bg-gray-200 animate-pulse" />
                <div className="w-[50px] h-3 bg-gray-200 animate-pulse rounded mt-2" />
              </div>
              {i < 4 && <div className="flex-1 mt-[10px] mx-1 h-0 border-t border-dashed border-gray-200" />}
            </div>
          ))}
        </div>
      </div>

      <div className="px-4">
        {/* Order ID */}
        <div className="h-5 w-[200px] bg-gray-200 animate-pulse rounded mt-4" />

        {/* QR Card */}
        <div className="h-[99px] bg-gray-200 animate-pulse rounded-[10px] mt-4" />

        {/* Pickup Scheduled */}
        <div className="mt-4">
          <div className="h-5 w-[130px] bg-gray-200 animate-pulse rounded" />
          <div className="h-5 w-[180px] bg-gray-200 animate-pulse rounded mt-2" />
        </div>

        <div className="border-t border-dashed border-[#e0e0e0] mt-4" />

        {/* Items */}
        <div className="h-6 w-[60px] bg-gray-200 animate-pulse rounded mt-4" />
        {[1, 2].map((i) => (
          <div key={i} className="flex items-center gap-3 py-3">
            <div className="w-[54px] h-[54px] rounded-[12px] bg-gray-200 animate-pulse" />
            <div className="flex-1">
              <div className="h-4 w-[120px] bg-gray-200 animate-pulse rounded" />
              <div className="h-3 w-[80px] bg-gray-200 animate-pulse rounded mt-2" />
            </div>
            <div className="h-4 w-[60px] bg-gray-200 animate-pulse rounded" />
          </div>
        ))}

        <div className="border-t border-dashed border-[#e0e0e0] mt-2" />

        {/* Outlet */}
        <div className="h-6 w-[130px] bg-gray-200 animate-pulse rounded mt-4" />
        <div className="h-4 w-full bg-gray-200 animate-pulse rounded mt-2" />
        <div className="h-4 w-3/4 bg-gray-200 animate-pulse rounded mt-1" />

        <div className="border-t border-dashed border-[#e0e0e0] mt-4" />

        {/* Bill info */}
        <div className="h-6 w-[80px] bg-gray-200 animate-pulse rounded mt-4" />
        {[1, 2, 3, 4].map((i) => (
          <div key={i} className="flex items-center justify-between mt-3">
            <div className="h-4 w-[100px] bg-gray-200 animate-pulse rounded" />
            <div className="h-4 w-[60px] bg-gray-200 animate-pulse rounded" />
          </div>
        ))}
      </div>
    </div>
  )
}

export default function OrderDetailPage() {
  const { orderToken } = useParams<{ orderToken: string }>()
  const navigate = useNavigate()
  const { store } = useStore()

  const [data, setData] = useState<OrderDetailsData | null>(null)
  const [loading, setLoading] = useState(true)
  const [showQr, setShowQr] = useState(false)

  useEffect(() => {
    if (!orderToken) return
    getOrderDetails(orderToken)
      .then((res) => setData(res))
      .catch((err) => console.error('Failed to load order:', err))
      .finally(() => setLoading(false))
  }, [orderToken])

  if (loading) return <Skeleton />

  if (!data) {
    return (
      <div className="bg-white min-h-screen font-poppins flex items-center justify-center">
        <p className="text-muted text-[14px]">Order not found</p>
      </div>
    )
  }

  const { order } = data

  return (
    <div className="bg-white min-h-screen font-poppins pb-8">
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
            Order Details
          </h1>
        </div>
      </header>

      {/* Status Tracker */}
      <StatusTracker statuses={order.statuses} />

      <div className="px-4">
        {/* Order ID + Date */}
        <div className="flex items-center gap-2 mt-4">
          <p className="text-[16px] font-medium text-dark">ID {order.order_number}</p>
          <span className="text-dark text-[14px]">&bull;</span>
          <p className="text-[14px] text-dark">{order.formatted_date}</p>
        </div>

        {/* Pickup QR Card */}
        <button
          onClick={() => setShowQr(true)}
          className="w-full mt-4 bg-[#fff8f8] rounded-[10px] px-4 py-3 flex items-center justify-between text-left"
        >
          <div>
            <p className="text-[14px] font-bold text-body">Pickup QR</p>
            <p className="text-[12px] text-muted mt-0.5">Show this to pickup item(s).</p>
            <p className="text-[14px] text-body mt-1">
              Your pickup code is <span className="font-semibold">{order.pickup.code}</span>
            </p>
          </div>
          <div className="shrink-0 bg-[#f7e7e7] rounded-[4px] p-2 ml-3">
            <QRCode value={order.pickup.qr_data} size={52} />
          </div>
        </button>

        {/* Pickup Info */}
        <div className="mt-4">
          <div className="flex items-center justify-between">
            <p className="text-[14px] font-semibold text-body">
              {order.pickup.formatted_ready_at ? 'Pickup Scheduled' : 'Pickup Instant'}
            </p>
            <span
              className="text-[12px] font-semibold px-3 py-1 rounded-full"
              style={{
                color: order.status_color,
                backgroundColor: `${order.status_color}15`,
              }}
            >
              {order.status_label}
            </span>
          </div>
          {order.pickup.formatted_ready_at && (
            <p className="text-[16px] text-body mt-1">
              {order.pickup.formatted_ready_at}
            </p>
          )}
        </div>

        <div className="border-t border-dashed border-[#e0e0e0] mt-4" />

        {/* Items */}
        <p className="text-[18px] font-semibold text-dark mt-4">Items</p>
        <div className="mt-2">
          {order.items.map((item, i) => (
            <div key={item.id}>
              <div className="flex items-center gap-3 py-3">
                {/* Thumbnail */}
                <div className="relative w-[54px] h-[54px] shrink-0 rounded-[12px] overflow-hidden">
                  <img
                    src={item.thumbnail}
                    alt={item.name}
                    className="w-full h-full object-cover"
                  />
                  <img
                    src={item.is_vegetarian ? vegIcon : nonvegIcon}
                    alt={item.is_vegetarian ? 'Veg' : 'Non-veg'}
                    className="absolute top-0.5 right-0.5 w-[12px] h-[12px]"
                  />
                </div>

                {/* Info */}
                <div className="flex-1 min-w-0">
                  <p className="text-[14px] text-dark leading-5">{item.name}</p>
                  <div className="flex items-center gap-1 mt-1">
                    <span className="text-[12px] text-[#8f8f8f]">QTY: {item.quantity}</span>
                    {item.quantity > 1 && (
                      <span className="text-[12px] text-[#8f8f8f]">
                        (₹{item.price} X {item.quantity})
                      </span>
                    )}
                  </div>
                </div>

                {/* Price */}
                <p className="text-[14px] font-medium text-dark shrink-0">
                  ₹{item.total_price.toFixed(2)}
                </p>
              </div>
              {i < order.items.length - 1 && <div className="border-t border-border" />}
            </div>
          ))}
        </div>

        <div className="border-t border-dashed border-[#e0e0e0] mt-2" />

        {/* Outlet Address */}
        <div className="mt-4">
          <p className="text-[18px] font-semibold text-dark">Outlet address</p>
          <p className="text-[14px] text-muted leading-[20px] mt-1">
            {order.store.address || store?.address || ''}
          </p>
        </div>

        <div className="border-t border-dashed border-[#e0e0e0] mt-4" />

        {/* Bill Info */}
        <div className="mt-4">
          <p className="text-[18px] font-semibold text-dark">Bill info</p>

          <div className="flex items-center justify-between mt-3">
            <p className="text-[14px] text-body leading-[20px]">Price</p>
            <p className="text-[14px] text-dark">₹{order.pricing.formatted_subtotal}</p>
          </div>

          <div className="flex items-center justify-between mt-2">
            <p className="text-[14px] text-body leading-[20px]">GST</p>
            <p className="text-[14px] text-dark">₹{order.pricing.formatted_tax}</p>
          </div>

          <div className="border-t border-dashed border-[#e0e0e0] mt-3" />

          <div className="flex items-center justify-between mt-3">
            <p className="text-[14px] text-dark">Amount to be paid</p>
            <p className="text-[14px] text-dark">₹{order.pricing.formatted_total}</p>
          </div>

          <div className="border-t border-dashed border-[#e0e0e0] mt-3" />

          <div className="flex items-center justify-between mt-3">
            <p className="text-[14px] text-dark">Total paid amount</p>
            <p className="text-[18px] font-bold text-primary-red">₹{order.pricing.formatted_online_paid}</p>
          </div>
        </div>
      </div>

      {/* QR Bottom Sheet */}
      {showQr && (
        <QrBottomSheet
          qrData={order.pickup.qr_data}
          pickupCode={order.pickup.code}
          onClose={() => setShowQr(false)}
        />
      )}
    </div>
  )
}
