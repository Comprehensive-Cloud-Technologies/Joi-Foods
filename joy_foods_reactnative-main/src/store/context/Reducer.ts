import { IAppCtx, Prettify } from 'types';
import {
  HIDE_SPLASH, SET_SNACKBAR, SET_CATEGORY_LIST, SET_PRODUCT_LIST, SET_CART_DATA, SET_PRODUCT_DETAILS,
  SET_PROFILE_DATA, SET_MISC_DATA, SET_ORDER_DATA, RESET_APP_STATE,
} from './Actions';
import { IAction } from './Types';

const intlReducer = (
  state: Prettify<IAppCtx>,
  { type, isSplashHide, snackbar, categoryList, productList, cartData, productDetails, profileData, miscData, orderData, }: IAction,
): IAppCtx => {
  switch (type) {
    case HIDE_SPLASH: {
      return isSplashHide !== undefined ? { ...state, isSplashHide } : state;
    }

    case SET_SNACKBAR: {
      return snackbar !== undefined ? { ...state, snackbar } : state;
    }

    case SET_CATEGORY_LIST: {
      return categoryList !== undefined ? { ...state, categoryList } : state;
    }

    case SET_PRODUCT_LIST: {
      return productList !== undefined ? { ...state, productList } : state;
    }

    case SET_CART_DATA: {
      return cartData !== undefined ? { ...state, cartData } : state;
    }

    case SET_PRODUCT_DETAILS: {
      return productDetails !== undefined
        ? { ...state, productDetails }
        : state;
    }

    case SET_PROFILE_DATA: {
      return profileData !== undefined ? { ...state, profileData } : state;
    }

    case SET_MISC_DATA: {
      return miscData !== undefined ? { ...state, miscData } : state;
    }

    case SET_ORDER_DATA: {
      return orderData !== undefined ? { ...state, orderData } : state;
    }

    case RESET_APP_STATE: {
      return {
        ...state,
        cartData: undefined,
        orderData: undefined,
        productDetails: undefined,
        profileData: undefined,
        productList: undefined,
        categoryList: undefined,

      };
    }
    default:
      return state;
  }
};

export default intlReducer;
