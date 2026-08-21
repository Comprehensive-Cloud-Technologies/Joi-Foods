import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter, Routes, Route } from 'react-router-dom'
import { Toaster } from 'sonner'
import './index.css'
import logo from './assets/images/logo.png'
import { StoreProvider } from './contexts/StoreContext.tsx'
import HomePage from './pages/HomePage.tsx'
import CategoriesListPage from './pages/CategoriesListPage.tsx'
import ProductsListPage from './pages/ProductsListPage.tsx'
import ProductDetailPage from './pages/ProductDetailPage.tsx'
import CartPage from './pages/CartPage.tsx'
import SearchPage from './pages/SearchPage.tsx'
import OrderSummaryPage from './pages/OrderSummaryPage.tsx'
import OrderSuccessPage from './pages/OrderSuccessPage.tsx'
import OrderDetailPage from './pages/OrderDetailPage.tsx'
import WelcomePage from './pages/WelcomePage.tsx'

function StoreLayout() {
  return (
    <StoreProvider>
      <Routes>
        <Route index element={<HomePage />} />
        <Route path="categories" element={<CategoriesListPage />} />
        <Route path="category/:categoryId" element={<ProductsListPage />} />
        <Route path="product/:productId" element={<ProductDetailPage />} />
        <Route path="cart" element={<CartPage />} />
        <Route path="search" element={<SearchPage />} />
        <Route path="order-summary" element={<OrderSummaryPage />} />
        <Route path="order-success" element={<OrderSuccessPage />} />
        <Route path="order/:orderToken" element={<OrderDetailPage />} />
      </Routes>
    </StoreProvider>
  )
}

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <Toaster position="top-center" richColors toastOptions={{ style: { fontFamily: "'Poppins', sans-serif", fontSize: 14 } }} />

    {/* Desktop gate — visible only on screens > 768px */}
    <div className="hidden lg:flex fixed inset-0 z-[9999] bg-white font-poppins flex-col items-center justify-center px-6 text-center">
      <img src={logo} alt="JOI Foods" className="w-[80px] h-[80px] rounded-full object-cover" />
      <h1 className="text-[22px] font-bold text-dark mt-6">Mobile Only</h1>
      <p className="text-[14px] text-muted mt-2 max-w-[320px] leading-[22px]">
        This app is designed for mobile and tablet devices. Please open this link on your phone or scan the QR code to get started.
      </p>
      <div className="mt-6 w-[60px] h-[100px] rounded-[14px] border-[3px] border-dark relative">
        <div className="absolute top-1 left-1/2 -translate-x-1/2 w-[20px] h-[3px] rounded-full bg-dark" />
      </div>
    </div>

    {/* App — visible only on mobile/tablet */}
    <div className="lg:hidden">
      <BrowserRouter>
        <Routes>
          <Route path="/" element={<WelcomePage />} />
          <Route path="/:storeCode/*" element={<StoreLayout />} />
        </Routes>
      </BrowserRouter>
    </div>
  </StrictMode>,
)
