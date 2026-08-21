import { apiPost } from './client'
import type {
  StoreInfoData,
  BannersData,
  CategoriesData,
  FeaturedProductsData,
} from '../types/api'

export function getStoreInfo(storeCode: string) {
  return apiPost<StoreInfoData>('/home/store_info', { store_code: storeCode })
}

export function getBanners(storeCode: string) {
  return apiPost<BannersData>('/home/banners', { store_code: storeCode })
}

export function getCategories(storeCode: string) {
  return apiPost<CategoriesData>('/home/categories', { store_code: storeCode })
}

export function getFeaturedProducts(storeCode: string, limit?: number) {
  return apiPost<FeaturedProductsData>('/home/featured', {
    store_code: storeCode,
    limit,
  })
}
