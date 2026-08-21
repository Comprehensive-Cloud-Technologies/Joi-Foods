import { apiPost } from './client'
import type {
  CartAddData,
  CartUpdateData,
  CartRemoveData,
  CartListData,
  CartCountData,
} from '../types/api'

export function addToCart(
  storeCode: string,
  productId: number,
  quantity?: number,
  note?: string,
) {
  return apiPost<CartAddData>('/cart/add', {
    store_code: storeCode,
    product_id: productId,
    quantity,
    note,
  })
}

export function incrementCart(cartId: number) {
  return apiPost<CartUpdateData>('/cart/increment', { cart_id: cartId })
}

export function decrementCart(cartId: number) {
  return apiPost<CartUpdateData>('/cart/decrement', { cart_id: cartId })
}

export function removeFromCart(cartId: number) {
  return apiPost<CartRemoveData>('/cart/remove', { cart_id: cartId })
}

export function getCartItems(storeCode: string) {
  return apiPost<CartListData>('/cart/list_items', { store_code: storeCode })
}

export function getCartCount(storeCode: string) {
  return apiPost<CartCountData>('/cart/count', { store_code: storeCode })
}
