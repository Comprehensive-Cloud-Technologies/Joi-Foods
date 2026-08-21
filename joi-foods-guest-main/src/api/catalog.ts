import { apiPost } from './client'
import type {
  CategoriesData,
  ProductsListData,
  ProductDetailData,
  SearchData,
} from '../types/api'

export function getCatalogCategories(storeCode: string) {
  return apiPost<CategoriesData>('/catalog/categories', {
    store_code: storeCode,
  })
}

export function getProducts(
  storeCode: string,
  categoryId: number,
  page?: number,
  perPage?: number,
) {
  return apiPost<ProductsListData>('/catalog/products', {
    store_code: storeCode,
    category_id: categoryId,
    page,
    per_page: perPage,
  })
}

export function getProductDetail(storeCode: string, productId: number) {
  return apiPost<ProductDetailData>('/catalog/product_detail', {
    store_code: storeCode,
    product_id: productId,
  })
}

export function searchProducts(
  storeCode: string,
  keyword: string,
  page?: number,
  perPage?: number,
) {
  return apiPost<SearchData>('/catalog/search', {
    store_code: storeCode,
    keyword,
    page,
    per_page: perPage,
  })
}
