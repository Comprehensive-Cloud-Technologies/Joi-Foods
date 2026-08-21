// Generic API response wrapper
export interface ApiResponse<T> {
  status: number
  success: boolean
  message: string
  data: T | null
}

// Store
export interface Store {
  id: number
  store_code: string
  name: string
  short_name: string
  thumbnail: string
  address: string
  phone: string
  company_name: string
  store_type: 'QSR' | 'KOT'
  is_operational: boolean
}

// Banner
export interface Banner {
  id: number
  title: string
  description: string
  image_url: string
  action: {
    type: string
    payload: string
  }
  display_order: number
}

// Category
export interface Category {
  id: number
  name: string
  description: string
  icon: string
  thumbnail: string
  is_primary: boolean
  display_order: number
}

// Product (list/featured)
export interface Product {
  id: number
  name: string
  short_name: string
  description: string
  ingredients: string
  thumbnail: string
  images: string[]
  price: number
  base_price: number
  discount_price: number | null
  tax_percentage: number
  is_vegetarian: boolean
  is_vegan: boolean
  calories: number
  is_featured: boolean
  is_popular: boolean
  is_in_stock: boolean
  available_stock: number | null
  is_in_cart: boolean
  cart_id: number | null
  cart_quantity: number
  category: {
    id: number
    name: string
  }
  meal_times?: {
    breakfast: boolean
    lunch: boolean
    dinner: boolean
  }
}

// Cart
export interface CartItem {
  cart_id: number
  product_id: number
  product_name: string
  short_name: string
  thumbnail: string
  quantity: number
  unit_price: number
  tax_percentage: number
  base_price: number
  tax_amount: number
  subtotal: number
  total: number
  note: string
  is_vegetarian: boolean
  is_vegan: boolean
  is_in_stock: boolean
  available_stock: number | null
}

export interface CartSummary {
  total_items: number
  item_count: number
  subtotal: number
  tax_amount: number
  total_amount: number
  amount_payable: number
  formatted: {
    subtotal: string
    tax_amount: string
    total_amount: string
    amount_payable: string
  }
}

export interface Pagination {
  current_page: number
  per_page: number
  total_count: number
  total_pages: number
  has_next: boolean
  has_previous: boolean
}

// Response data types
export interface StoreInfoData {
  store: Store
}

export interface BannersData {
  banners: Banner[]
  total_count: number
}

export interface CategoriesData {
  categories: Category[]
  total_count: number
}

export interface FeaturedProductsData {
  products: Product[]
  total_count: number
}

export interface ProductsListData {
  products: Product[]
  pagination: Pagination
}

export interface ProductDetailData {
  product: Product
}

export interface SearchData {
  keyword: string
  products: Product[]
  total_count: number
}

export interface CartAddData {
  session_id: string
  is_new_session: boolean
  cart_id: number
  product_id: number
  product_name: string
  thumbnail: string
  quantity: number
  price: number
  cart_count: number
}

export interface CartUpdateData {
  cart_id: number
  quantity: number
  removed?: boolean
}

export interface CartRemoveData {
  cart_id: number
  removed: boolean
}

export interface CartListData {
  items: CartItem[]
  summary: CartSummary
  has_stock_issue: boolean
}

export interface CartCountData {
  count: number
}

// Order types
export interface DeliveryLocation {
  id: number
  name: string
  short_name: string
  floor: string
  building: string
}

export interface DeliveryLocationsData {
  locations: DeliveryLocation[]
  total: number
}

export interface InitiateOrderData {
  pending_order_id: number
  razorpay_order_id: string
  razorpay_key: string
  amount: number
  currency: string
  guest_name: string
  guest_phone: string
  delivery_location: DeliveryLocation | null
  summary: {
    subtotal: number
    tax_amount: number
    total_amount: number
    amount_payable: number
    items_count: number
    formatted: {
      subtotal: string
      tax_amount: string
      total_amount: string
      amount_payable: string
    }
  }
}

export interface CompleteOrderData {
  order: {
    id: number
    order_number: string
    order_token: string
    pickup_code: string
    status: string
    total_amount: number
    online_paid: number
    items_count: number
    qr_data: string
    guest_name: string
    guest_phone: string
    created_at: string
  }
  store: {
    id: number
    name: string
    store_type: string
    address: string
  }
  delivery_location: DeliveryLocation | null
  pricing: {
    subtotal: number
    tax_amount: number
    total_amount: number
    online_paid: number
    formatted_subtotal: string
    formatted_tax: string
    formatted_total: string
    formatted_online_paid: string
  }
  payment: {
    method: string
    status: string
  }
}

export interface OrderStatus {
  code: string
  text: string
  is_completed: boolean
  is_current: boolean
}

export interface OrderDetailsData {
  order: {
    id: number
    order_number: string
    order_token: string
    module: string
    status: string
    status_label: string
    status_color: string
    statuses: OrderStatus[]
    pickup_code: string
    store: {
      name: string
      store_type: string
      address: string
      phone: string
    }
    delivery_location: DeliveryLocation | null
    guest: {
      name: string
      phone: string
    }
    items: {
      id: number
      product_id: number
      name: string
      short_name: string
      thumbnail: string
      quantity: number
      price: number
      tax_amount: number
      total_price: number
      is_vegetarian: boolean
      notes: string
    }[]
    items_count: number
    pickup: {
      code: string
      qr_data: string
      ready_at: string | null
      formatted_ready_at: string | null
    }
    pricing: {
      subtotal: number
      tax: number
      total: number
      online_paid: number
      formatted_subtotal: string
      formatted_tax: string
      formatted_total: string
      formatted_online_paid: string
    }
    payment: {
      method: string
      status: string
    }
    refund: {
      amount: number
      status: string | null
      formatted_amount: string
    }
    created_at: string
    formatted_date: string
  }
}
