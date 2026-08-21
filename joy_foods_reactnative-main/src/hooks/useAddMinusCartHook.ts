import { POSTreq } from 'api';
import { ConfirmationAlertRefT, useLoader, useSnackbar } from 'components';
import { useAtom } from 'jotai';
import { useContext, useRef } from 'react';
import { Alert } from 'react-native';
import { AppCtx, storeDataAtom } from 'store';
import {
  SET_CART_DATA,
  SET_PRODUCT_DETAILS,
  SET_PRODUCT_LIST,
} from 'store/context';

const useAddMinusCartHook = () => {
  const [storeData] = useAtom(storeDataAtom);
  const {
    dispatch,
    state: { productList, productDetails, cartData },
  } = useContext(AppCtx);
  const { showLoader, hideLoader } = useLoader();
  const { showSnackbar } = useSnackbar();
  const alertRef = useRef<ConfirmationAlertRefT>(null);

  const addToCart = async (
    id: string,
  ): Promise<{ data: any; success: boolean }> => {
    try {
      const payload = {
        store_id: storeData?.id,
        product_id: id,
        module: storeData?.store_type,
      };
      console.log('Add to Cart Payload::', JSON.stringify(payload, null, 3));
      const { data, success } = await POSTreq('cart/add', payload, true);
      console.log('Add to Cart Response::', JSON.stringify(data, null, 3));
      if (!success) {
        alertRef.current?.open('Info', data?.message ?? 'Failed to add item to cart', "OK");
        return { data: null, success: false };
      }
      return { data, success };
    } catch (error) {
      console.log('ERROR::', error);
      return { data: null, success: false };
    }
  };

  const increaseQuantity = async (
    cartId: string,
  ): Promise<{ data: any; success: boolean }> => {
    try {
      const { data, success } = await POSTreq(
        'cart/increment',
        {
          cart_id: cartId,
        },
        true,
      );
      console.log(
        'Increase Quantity Response::',
        JSON.stringify(data, null, 3),
      );
      if (!success) {
        alertRef.current?.open('Info', data?.message ?? 'Failed to increase item quantity', "OK");
        // Alert.alert('Info', data?.message ?? 'Failed to increase item quantity');
        return { data: null, success: false };
      }
      return { data, success };
    } catch (error) {
      console.log('ERROR::', error);
      return { data: null, success: false };
    }
  };

  const decreaseQuantity = async (
    cartId: string,
  ): Promise<{ data: any; success: boolean }> => {
    try {
      const { data, success } = await POSTreq(
        'cart/decrement',
        {
          cart_id: cartId,
        },
        true,
      );
      console.log(
        'Decrease Quantity Response::',
        JSON.stringify(data, null, 3),
      );
      if (!success) {
        alertRef.current?.open('Info', data?.message ?? 'Failed to decrease item quantity', "OK");
        // Alert.alert('Info', data?.message ?? 'Failed to decrease item quantity');
        return { data: null, success: false };
      }
      return { data, success };
    } catch (error) {
      console.log('ERROR::', error);
      return { data: null, success: false };
    }
  };

  const deleteCartItem = async (cartId: number) => {
    try {
      showLoader();
      const payload = {
        cart_id: cartId,
      };
      const { success, data } = await POSTreq('cart/remove', payload, true);
      console.log('Delete Cart Item::', success, JSON.stringify(data, null, 3));
      if (success) {
        showSnackbar(data?.message);
        const updatedCartData = cartData?.items?.filter(
          item => item.cart_id !== cartId,
        );
        dispatch({
          type: SET_CART_DATA,
          cartData: {
            ...cartData,
            items: updatedCartData,
            summary: {
              ...cartData?.summary,
              total_items: (cartData?.summary?.total_items ?? 0) - 1,
            },
          },
        });
        const updatedProductList = productList?.map(product => {
          if (product?.cart_id === cartId) {
            return {
              ...product,
              cart_quantity: 0,
              is_in_cart: false,
            };
          }
          return product;
        });
        dispatch({
          type: SET_PRODUCT_LIST,
          productList: updatedProductList,
        });
        const updatedProduct = {
          ...productDetails,
          cart_quantity: 0,
          is_in_cart: false,
        };
        dispatch({
          type: SET_PRODUCT_DETAILS,
          productDetails: updatedProduct,
        });
      } else {
        alertRef.current?.open('Info', data?.message ?? 'Failed to delete item from cart', "OK");
        // Alert.alert('Info', data?.message ?? 'Failed to delete item from cart');
      }
      hideLoader();
    } catch (error) {
      console.log('Delete Cart Item Error::', error);
      hideLoader();
    }
  };

  const handleAddToCart = async (
    id: string,
    setCartId?: (cartId: number) => void,
  ) => {
    const { data, success } = await addToCart(id);
    if (success) {
      const updatedProductList = productList?.map(product => {
        if (product?.id === parseInt(id)) {
          return {
            ...product,
            is_in_cart: true,
            cart_quantity: 1,
            cart_id: data?.data?.cart_id,
          };
        }
        return product;
      });
      dispatch({
        type: SET_PRODUCT_LIST,
        productList: updatedProductList,
      });
      setCartId?.(data?.data?.cart_id);
      const updatedProduct = {
        ...productDetails,
        is_in_cart: true,
        cart_quantity: 1,
        cart_id: data?.data?.cart_id,
      };
      dispatch({
        type: SET_PRODUCT_DETAILS,
        productDetails: updatedProduct,
      });
    }
  };

  const handleIncrement = async (
    cartId: string,
    setCartQuantity?: (quantity: number) => void,
  ) => {
    const { data, success } = await increaseQuantity(cartId);
    if (success) {
      if (productDetails?.cart_id == parseInt(cartId)) {
        dispatch({
          type: SET_PRODUCT_DETAILS,
          productDetails: {
            ...productDetails,
            cart_quantity: data?.data?.quantity,
            is_in_cart: data?.data?.quantity > 0,
          },
        });
      }
      const updatedProductList = productList?.map(product => {
        if (product?.cart_id === parseInt(cartId)) {
          return {
            ...product,
            cart_quantity: data?.data?.quantity,
          };
        }
        return product;
      });
      dispatch({
        type: SET_PRODUCT_LIST,
        productList: updatedProductList,
      });

      const updatedCartData = cartData?.items?.map(item => {
        if (item?.cart_id === parseInt(cartId)) {
          return {
            ...item,
            quantity: data?.data?.quantity,
          };
        }
        return item;
      });
      dispatch({
        type: SET_CART_DATA,
        cartData: {
          ...cartData,
          items: updatedCartData,
          summary: {
            ...cartData?.summary,
            total_items: (cartData?.summary?.total_items ?? 0) + 1,
          },
        },
      });

      dispatch({
        type: SET_PRODUCT_DETAILS,
        productDetails: {
          ...productDetails,
          cart_quantity: data?.data?.quantity,
        },
      });

      setCartQuantity?.(data?.data?.quantity);
    }
  };

  const handleDecrement = async (
    cartId: string,
    setCartQuantity?: (quantity: number) => void,
  ) => {
    const { data, success } = await decreaseQuantity(cartId);
    if (success) {
      const updatedProductList = productList?.map(product => {
        if (product?.cart_id === parseInt(cartId)) {
          return {
            ...product,
            cart_quantity: data?.data?.quantity,
            is_in_cart: data?.data?.quantity > 0,
          };
        }
        return product;
      });
      dispatch({
        type: SET_PRODUCT_LIST,
        productList: updatedProductList,
      });
      setCartQuantity?.(data?.data?.quantity);

      dispatch({
        type: SET_PRODUCT_DETAILS,
        productDetails: {
          ...productDetails,
          cart_quantity: data?.data?.quantity,
          is_in_cart: data?.data?.quantity > 0,
        },
      });

      if (data?.data?.quantity) {
        const updatedCartData = cartData?.items?.map(item => {
          if (item?.cart_id === parseInt(cartId)) {
            return {
              ...item,
              quantity: data?.data?.quantity,
            };
          }
          return item;
        });
        dispatch({
          type: SET_CART_DATA,
          cartData: {
            ...cartData,
            items: updatedCartData,
            summary: {
              ...cartData?.summary,
              total_items: (cartData?.summary?.total_items ?? 0) - 1,
            },
          },
        });
      } else {
        const updatedCartData = cartData?.items?.filter(
          item => item.cart_id !== parseInt(cartId),
        );
        dispatch({
          type: SET_CART_DATA,
          cartData: {
            ...cartData,
            items: updatedCartData,
            summary: {
              ...cartData?.summary,
              total_items: (cartData?.summary?.total_items ?? 0) - 1,
            },
          },
        });
      }

      if (productDetails?.cart_id == parseInt(cartId)) {
        dispatch({
          type: SET_PRODUCT_DETAILS,
          productDetails: {
            ...productDetails,
            cart_quantity: data?.data?.quantity,
            is_in_cart: data?.data?.quantity > 0,
          },
        });
      }
    }
  };

  return {
    addToCart,
    increaseQuantity,
    decreaseQuantity,
    deleteCartItem,
    handleAddToCart,
    handleIncrement,
    handleDecrement,
    alertRef,
  };
};

export default useAddMinusCartHook;
