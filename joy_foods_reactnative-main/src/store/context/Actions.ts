import { Dispatch } from 'react';
import { IAction } from './Types';
import { CategoryListT, ICartList, IProductDetail, ISnackbar, MiscDataT, OrderItemT, ProductListT, ProfileDataT } from 'types';

enum ActionType {
  HIDE_SPLASH = 'HIDE_SPLASH',
  IS_AUTH_PASS = 'IS_AUTH_PASS',
  SET_SNACKBAR = 'SET_SNACKBAR',
  SET_CATEGORY_LIST = 'SET_CATEGORY_LIST',
  SET_PRODUCT_LIST = 'SET_PRODUCT_LIST',
  SET_CART_DATA = 'SET_CART_DATA',
  SET_PRODUCT_DETAILS = 'SET_PRODUCT_DETAILS',
  SET_PROFILE_DATA = 'SET_PROFILE_DATA',
  SET_MISC_DATA = 'SET_MISC_DATA',
  SET_ORDER_DATA = 'SET_ORDER_DATA',
  RESET_APP_STATE = 'RESET_APP_STATE',
}

const {
  HIDE_SPLASH, IS_AUTH_PASS, SET_SNACKBAR, SET_CATEGORY_LIST, SET_PRODUCT_LIST, SET_CART_DATA,
  SET_PRODUCT_DETAILS, SET_PROFILE_DATA, SET_MISC_DATA, SET_ORDER_DATA, RESET_APP_STATE
} = ActionType;

const set_hide_splash = (dispatch: Dispatch<IAction>, isSplashHide: boolean) =>
  dispatch({ type: HIDE_SPLASH, isSplashHide });

const set_is_auth_pass = (dispatch: Dispatch<IAction>, isAuthPass: boolean) =>
  dispatch({ type: IS_AUTH_PASS, isAuthPass });

const set_snackbar = (dispatch: Dispatch<IAction>, snackbar: ISnackbar) =>
  dispatch({ type: SET_SNACKBAR, snackbar });

const set_category_list = (
  dispatch: Dispatch<IAction>,
  categoryList: CategoryListT[],
) => dispatch({ type: SET_CATEGORY_LIST, categoryList });

const set_product_list = (
  dispatch: Dispatch<IAction>,
  productList: ProductListT[],
) => dispatch({ type: SET_PRODUCT_LIST, productList });

const set_cart_data = (dispatch: Dispatch<IAction>, cartData: ICartList) =>
  dispatch({ type: SET_CART_DATA, cartData });

const set_product_details = (dispatch: Dispatch<IAction>, productDetails: IProductDetail) =>
  dispatch({ type: SET_PRODUCT_DETAILS, productDetails });

const set_profile_data = (dispatch: Dispatch<IAction>, profileData: ProfileDataT) =>
  dispatch({ type: SET_PROFILE_DATA, profileData });

const set_misc_data = (dispatch: Dispatch<IAction>, miscData: MiscDataT) =>
  dispatch({ type: SET_MISC_DATA, miscData });

const set_order_data = (dispatch: Dispatch<IAction>, orderData: OrderItemT[]) =>
  dispatch({ type: SET_ORDER_DATA, orderData });

const reset_app_state = (dispatch: Dispatch<IAction>) =>
  dispatch({ type: RESET_APP_STATE });

export {
  ActionType,
  set_hide_splash, HIDE_SPLASH,
  set_is_auth_pass, IS_AUTH_PASS,
  set_snackbar, SET_SNACKBAR,
  set_category_list, SET_CATEGORY_LIST,
  set_product_list, SET_PRODUCT_LIST,
  set_cart_data, SET_CART_DATA,
  set_product_details, SET_PRODUCT_DETAILS,
  set_profile_data, SET_PROFILE_DATA,
  set_misc_data, SET_MISC_DATA,
  set_order_data, SET_ORDER_DATA,
  reset_app_state, RESET_APP_STATE,
};
