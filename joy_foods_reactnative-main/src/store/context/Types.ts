import { Dispatch } from 'react';
import { CategoryListT, IAppCtx, ICartList, IProductDetail, ISnackbar, MiscDataT, OrderItemT, ProductListT, ProfileDataT } from 'types';
import { ActionType } from './Actions';

interface IContext {
  state: IAppCtx;
  dispatch: Dispatch<IAction>;
}

type IAction = {
  type: ActionType;
  isSplashHide?: boolean;
  isAuthPass?: boolean;
  snackbar?: ISnackbar;
  categoryList?: CategoryListT[];
  productList?: ProductListT[];
  cartData?: ICartList;
  productDetails?: IProductDetail;
  profileData?: ProfileDataT;
  miscData?: MiscDataT;
  orderData?: OrderItemT[];
};

export type { IContext, IAction };
