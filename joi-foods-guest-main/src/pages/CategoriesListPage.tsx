import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'

import backArrowIcon from '../assets/icons/back-arrow.svg'

import { useStore } from '../contexts/StoreContext'
import { getCatalogCategories } from '../api/catalog'
import type { Category } from '../types/api'

export default function CategoriesListPage() {
  const navigate = useNavigate()
  const { storeCode } = useStore()

  const [categories, setCategories] = useState<Category[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    if (!storeCode) return

    setLoading(true)
    getCatalogCategories(storeCode)
      .then((data) => setCategories(data.categories))
      .catch((err) => console.error('Failed to load categories:', err))
      .finally(() => setLoading(false))
  }, [storeCode])

  return (
    <div className="bg-white min-h-screen font-poppins">
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
            Category
          </h1>
        </div>
      </header>

      {/* Categories Grid */}
      <div className="px-4 mt-4 pb-8">
        {loading ? (
          <div className="flex items-center justify-center py-12">
            <p className="text-muted text-[14px]">Loading categories...</p>
          </div>
        ) : categories.length === 0 ? (
          <div className="flex items-center justify-center py-12">
            <p className="text-muted text-[14px]">No categories available</p>
          </div>
        ) : (
          <div className="grid grid-cols-3 gap-3">
            {categories.map((cat) => (
              <button
                key={cat.id}
                onClick={() => navigate(`/${storeCode}/category/${cat.id}`)}
                className="h-[117px] bg-white rounded-[17px] border border-[#ececec] flex flex-col items-center justify-center gap-1"
              >
                <img
                  src={cat.thumbnail || cat.icon}
                  alt={cat.name}
                  className="w-[52px] h-[52px] object-contain"
                />
                <span className="text-[12px] text-dark leading-normal text-center px-1">
                  {cat.name}
                </span>
              </button>
            ))}
          </div>
        )}
      </div>
    </div>
  )
}
