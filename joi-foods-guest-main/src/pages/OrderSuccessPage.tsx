import { useState, useEffect } from 'react'
import { useLocation, useNavigate } from 'react-router-dom'
// @ts-expect-error: runtime named export exists but types only declare default
import { QRCode } from 'react-qr-code'

import { useStore } from '../contexts/StoreContext'
import type { CompleteOrderData } from '../types/api'

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
      {/* Overlay */}
      <div
        className={`absolute inset-0 bg-[#2d2b2b] transition-opacity duration-300 ${visible ? 'opacity-72' : 'opacity-0'}`}
        onClick={handleClose}
      />

      {/* Sheet */}
      <div className={`absolute bottom-0 left-0 right-0 bg-white rounded-t-[20px] px-5 pt-3 pb-8 transition-transform duration-300 ease-out ${visible ? 'translate-y-0' : 'translate-y-full'}`}>
        {/* Drag handle */}
        <div className="flex justify-center mb-4">
          <div className="w-[35px] h-[4px] rounded-full bg-[#d9d9d9]" />
        </div>

        <h2 className="text-[22px] font-bold text-dark text-center">Pickup QR</h2>
        <p className="text-[14px] text-body text-center mt-1">Show this to pickup item(s).</p>

        {/* QR Code */}
        <div className="flex justify-center mt-5">
          <div className="bg-[#fff8f8] rounded-[4px] p-4">
            <QRCode value={qrData} size={200} />
          </div>
        </div>

        {/* Pickup code */}
        <div className="text-center mt-5">
          <p className="text-[14px] text-body">Your pickup code is</p>
          <p className="text-[18px] font-semibold text-body">{pickupCode}</p>
        </div>

        {/* Close button */}
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

export default function OrderSuccessPage() {
  const navigate = useNavigate()
  const { storeCode } = useStore()
  const location = useLocation()
  const orderData = (location.state as { orderData?: CompleteOrderData })?.orderData

  const [showQr, setShowQr] = useState(false)

  if (!orderData) {
    return (
      <div className="bg-white min-h-screen font-poppins flex items-center justify-center">
        <p className="text-muted text-[14px]">No order data found</p>
      </div>
    )
  }

  const { order } = orderData

  return (
    <div className="bg-white min-h-screen font-poppins flex flex-col items-center px-6">
      {/* Success Icon */}
      <div className="mt-24 w-[106px] h-[106px] rounded-full border-[3px] border-primary-red flex items-center justify-center">
        <svg width="40" height="30" viewBox="0 0 40 30" fill="none">
          <path d="M3 15L15 27L37 3" stroke="#bd3839" strokeWidth="4" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
      </div>

      {/* Title */}
      <h1 className="text-[24px] font-bold text-dark mt-6">Order Placed</h1>
      <p className="text-[14px] text-muted mt-2">Your order has been submitted successfully</p>

      {/* Order ID / Code */}
      <div className="flex items-center mt-8 w-full max-w-[300px]">
        <div className="flex-1 text-center">
          <p className="text-[14px] text-[#8f8f8f]">Order ID</p>
          <p className="text-[16px] font-medium text-dark mt-1">{order.order_number}</p>
        </div>
        <div className="w-[1px] h-[49px] bg-border" />
        <div className="flex-1 text-center">
          <p className="text-[14px] text-[#8f8f8f]">Order Code</p>
          <p className="text-[16px] font-medium text-dark mt-1">{order.pickup_code}</p>
        </div>
      </div>

      {/* Pickup QR Card */}
      <button
        onClick={() => setShowQr(true)}
        className="w-full max-w-[354px] mt-6 bg-[#fff8f8] rounded-[10px] px-4 py-3 flex items-center justify-between text-left"
      >
        <div>
          <p className="text-[14px] font-bold text-body">Pickup QR</p>
          <p className="text-[12px] text-muted mt-0.5">Show this to pickup item(s).</p>
          <p className="text-[14px] text-body mt-1">
            Your pickup code is <span className="font-semibold">{order.pickup_code}</span>
          </p>
        </div>
        <div className="shrink-0 bg-[#f7e7e7] rounded-[4px] p-2 ml-3">
          <QRCode value={order.qr_data} size={52} />
        </div>
      </button>

      {/* Action Buttons */}
      <div className="w-full max-w-[354px] mt-8 flex flex-col gap-3">
        <button
          onClick={() => navigate(`/${storeCode}/order/${order.order_token}`)}
          className="w-full h-[50px] rounded-[56px] border border-body text-[14px] font-semibold text-dark"
        >
          View Order Details
        </button>
        <button
          onClick={() => navigate(`/${storeCode}`)}
          className="w-full h-[50px] rounded-[56px] border border-body text-[14px] font-semibold text-dark"
        >
          Back to Home
        </button>
      </div>

      {/* Close */}
      <button
        onClick={() => navigate(`/${storeCode}`)}
        className="mt-6 mb-8 text-[14px] font-semibold text-dark underline"
      >
        Close
      </button>

      {/* QR Bottom Sheet */}
      {showQr && (
        <QrBottomSheet
          qrData={order.qr_data}
          pickupCode={order.pickup_code}
          onClose={() => setShowQr(false)}
        />
      )}
    </div>
  )
}
