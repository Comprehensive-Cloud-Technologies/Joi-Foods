import { apiPost } from './client'
import type {
  DeliveryLocationsData,
  InitiateOrderData,
  CompleteOrderData,
  OrderDetailsData,
} from '../types/api'

export function getDeliveryLocations(storeCode: string) {
  return apiPost<DeliveryLocationsData>('/orders/delivery_locations', {
    store_code: storeCode,
  })
}

export function initiateOrder(
  storeCode: string,
  guestName: string,
  guestPhone: string,
  deliveryLocationId?: number,
) {
  return apiPost<InitiateOrderData>('/orders/initiate', {
    store_code: storeCode,
    guest_name: guestName,
    guest_phone: guestPhone,
    delivery_location_id: deliveryLocationId,
  })
}

export function completeOrder(
  storeCode: string,
  razorpayOrderId: string,
  razorpayPaymentId: string,
  razorpaySignature: string,
) {
  return apiPost<CompleteOrderData>('/orders/complete', {
    store_code: storeCode,
    razorpay_order_id: razorpayOrderId,
    razorpay_payment_id: razorpayPaymentId,
    razorpay_signature: razorpaySignature,
  })
}

export function getOrderDetails(orderToken: string) {
  return apiPost<OrderDetailsData>('/orders/details', {
    order_token: orderToken,
  })
}
