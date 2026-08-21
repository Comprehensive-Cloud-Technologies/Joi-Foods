import vegIcon from '../assets/icons/veg.svg'
import nonvegIcon from '../assets/icons/nonveg.svg'
import plusIcon from '../assets/icons/plus.svg'
import minusIcon from '../assets/icons/minus.svg'
import plusDarkIcon from '../assets/icons/plus-dark.svg'
import type { Product } from '../types/api'

interface ProductCardProps {
  product: Product
  onAddToCart: (productId: number) => void
  onIncrement: (cartId: number) => void
  onDecrement: (cartId: number) => void
  onTap: (productId: number) => void
}

export default function ProductCard({
  product,
  onAddToCart,
  onIncrement,
  onDecrement,
  onTap,
}: ProductCardProps) {
  const inCart = product.is_in_cart && product.cart_quantity > 0

  return (
    <div className="min-w-0 bg-white rounded-xl border border-border">
      <div
        className="relative h-[177px] overflow-hidden rounded-t-xl cursor-pointer"
        onClick={() => onTap(product.id)}
      >
        <img
          src={product.thumbnail}
          alt={product.name}
          className="w-full h-full object-cover"
        />
        <img
          src={product.is_vegetarian ? vegIcon : nonvegIcon}
          alt={product.is_vegetarian ? 'Veg' : 'Non-veg'}
          className="absolute top-2 right-2 w-5 h-5"
        />
      </div>
      <div className="px-[11px] pt-[10px] pb-[12px]">
        <p className="text-[14px] font-medium text-body leading-4">{product.name}</p>
        <p
          className={`text-[10px] font-medium leading-4 mt-[3px] ${
            product.is_in_stock ? 'text-green' : 'text-red-stock'
          }`}
        >
          {product.is_in_stock ? 'In Stock' : 'Out of stock'}
        </p>
        <div className="flex items-end justify-between mt-[8px] h-8">
          <p className="text-[14px] font-semibold text-dark leading-4">
            ₹{product.price.toFixed(2)}
          </p>

          {!inCart ? (
            <button
              onClick={product.is_in_stock ? () => onAddToCart(product.id) : undefined}
              className={`w-8 h-8 rounded-full bg-orange flex items-center justify-center ${
                !product.is_in_stock ? 'opacity-40 cursor-not-allowed' : ''
              }`}
            >
              <img src={plusIcon} alt="Add to cart" className="w-[18px] h-[19px]" />
            </button>
          ) : (
            <div className="flex items-center gap-[10px]">
              <button
                onClick={() => onDecrement(product.cart_id!)}
                className="w-[26px] h-[26px] rounded-[6px] bg-[#e9e9e9] flex items-center justify-center"
              >
                <img src={minusIcon} alt="Remove" className="w-[8px] h-[2px]" />
              </button>
              <span className="text-[14px] font-bold text-[#23331d] w-[6px] text-center leading-none">
                {product.cart_quantity}
              </span>
              <button
                onClick={() => onIncrement(product.cart_id!)}
                className="w-[26px] h-[26px] rounded-[6px] bg-orange flex items-center justify-center"
              >
                <img src={plusDarkIcon} alt="Add" className="w-[9px] h-[9px]" />
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
