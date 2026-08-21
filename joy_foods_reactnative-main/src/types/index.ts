import { BottomTabScreenProps } from '@react-navigation/bottom-tabs';
import { CompositeScreenProps } from '@react-navigation/native';
import { StackNavigationProp, StackScreenProps } from '@react-navigation/stack';
import StoreType from './StoreTypes';

type Prettify<T> = {
  [K in keyof T]: T[K];
} & {};

type AppStackParamList = {
  SplashScr: undefined;
  OnboardingScreen: undefined;
  CompanyCode: undefined;
  LoginScreen: undefined;
  ForgotPasswordScreen: undefined;
  VerifyOtpScreen: { email: string };
  SetNewPasswordScreen: { reset_token: string; email: string };
  CreateAccount: undefined;
  TermsAndConditions: undefined;
  PrivacyPolicy: undefined;
  SelectStore: undefined;
  SelectStoreScr: { isFromStoreChange: boolean; } | undefined;
  Search: undefined;
  Home: undefined;
  AppNavigator: undefined;
  CategoryList: undefined;
  CategoryWiseProductList: { categoryID: number; categoryName: string };
  MyCart: undefined;
  PopularItemsList: undefined;
  ItemDetails: { itemId: number; isFromMyCart?: boolean };
  OrderSummary: { premealCheckData?: IProductCheck, reorderData?: ReorderT } | undefined;
  CMSController: {
    title: 'Terms & Conditions' | 'Privacy Policy' | 'About Us';
    content: string;
  };
  OrderSuccessfull: { orderData: OrderSuccessT, storeType?: string };
  OrderDetailsScr: { orderId: string };
  EditProfileScr: undefined;
  SettingsScr: undefined;
  ChangePasswordScr: undefined;
  MyWalletScr: undefined;
  SupportScr: { topics: string[] };
  SupportSuccessScr: { message: string };
  SupportContactScr: undefined;
  NoInternetScr: undefined;
  ScanQR: { isFromStoreChange?: boolean } | undefined;
  ScanQRCode: { isFromStoreChange?: boolean } | undefined;
};

type BottomTabParamList = {
  Menu: undefined;
  Orders: undefined;
  ScanQR: undefined;
  Notifications: undefined;
  Profile: undefined;
};

type StackProps<RouteName extends keyof AppStackParamList> = StackScreenProps<
  AppStackParamList,
  RouteName,
  'AppStack'
>;

type NavProps<RouteName extends keyof BottomTabParamList> =
  CompositeScreenProps<
    BottomTabScreenProps<BottomTabParamList, RouteName>,
    StackScreenProps<AppStackParamList>
  >;

export function navigateOrGoBackTo<
  T extends keyof AppStackParamList,
  K extends keyof AppStackParamList,
>(
  navigation: StackNavigationProp<AppStackParamList, T>,
  targetScreen: K,
  params?: AppStackParamList[K],
  shouldReplace: boolean = false,
) {
  if (shouldReplace) {
    (navigation as any).replace(targetScreen, params);
    return;
  }
  const state = navigation.getState();
  const routes = state?.routes ?? [];
  const targetIndex = routes.findIndex(route => route.name === targetScreen);

  if (targetIndex !== -1) {
    const screensAbove = routes.length - 1 - targetIndex;
    if (screensAbove > 0) {
      navigation.pop(screensAbove);
    }
  } else {
    if (params !== undefined) {
      (navigation as any).navigate(targetScreen, params);
    } else {
      (navigation as any).navigate(targetScreen);
    }
  }
}

interface IPrompt {
  title: string | null;
  success?: boolean;
}

type UserType = {};

interface IAppState {
  user: Prettify<UserType>;
  token: string;
  next_step: string;
  interests: string[];
  _prompt: IPrompt | null;
  _logout: { loggingOut: boolean; showAlert: boolean };
}

interface ISnackbar {
  visible: boolean;
  message: string;
  isErrorSnackBar: boolean;
}
interface ICompanyCode {
  id: string;
  company_name: string;
  company_code: string;
}

interface IStoreData {
  id: number;
  store_code: string;
  name: string;
  short_name: string;
  store_type: string;
  thumbnail: string;
  primary_email: string;
  primary_phone: string;
  address: {
    line1: string;
    city: string;
    state: string;
  };
  is_operational: boolean;
}

interface IUser {
  id: string;
  employee_code: string;
  first_name: string;
  last_name: string;
  email: string;
  company_id: string;
  kot_permission: boolean;
  qsr_access: boolean;
  premeal_access: boolean;
}

interface IAppCtx {
  isSplashHide: boolean;
  isAuthPass?: boolean;
  snackbar?: ISnackbar;
  categoryList?: CategoryListT[];
  productList?: ProductListT[];
  cartData?: ICartList;
  productDetails?: IProductDetail;
  profileData?: ProfileDataT;
  miscData?: MiscDataT;
  orderData?: OrderItemT[];
}

type IBanner = {
  id: number;
  title?: string;
  description?: string;
  image_url?: string;
  action?: {
    type: string;
    payload: string;
  };
  display_order?: number;
};

type ProductListT = {
  id?: number;
  name?: string;
  short_name?: string;
  description?: string;
  ingredients?: string;
  thumbnail?: string;
  images?: string[];
  price?: number;
  base_price?: number;
  discount_price?: number;
  tax_percentage?: number;
  is_vegetarian?: boolean;
  is_vegan?: boolean;
  calories?: number;
  meal_times?: {
    breakfast?: boolean;
    lunch?: boolean;
    dinner?: boolean;
  };
  is_featured?: boolean;
  is_popular?: boolean;
  is_in_stock?: boolean;
  is_in_cart?: boolean;
  cart_quantity?: number;
  cart_id?: number;
  category?: {
    id?: number;
    name?: string;
  };
  isPaidByCompany?: boolean;
  percentOffByCompany?: number;
};

type CategoryListT = {
  id: number;
  name: string;
  description: string;
  icon: string;
  thumbnail: string | null;
  is_primary: boolean;
  display_order: number;
};

type IProductDetail = {
  id?: number;
  name?: string;
  short_name?: string | null;
  description?: string;
  ingredients?: string;
  thumbnail?: string;
  images?: string[];
  price?: number;
  base_price?: number;
  discount_price?: number | null;
  tax_percentage?: number;
  is_vegetarian?: boolean;
  is_vegan?: boolean;
  calories?: number;
  meal_times?: {
    breakfast?: boolean;
    lunch?: boolean;
    dinner?: boolean;
  };
  is_featured?: boolean;
  is_popular?: boolean;
  is_in_stock?: boolean;
  is_in_cart?: boolean;
  cart_quantity?: number;
  cart_id?: number;
  category?: {
    id?: number;
    name?: string;
  };
  weekly_schedule?: {
    schedule?: {
      day?: string;
      available?: boolean;
      display_order?: number;
      menu_items?: {
        items?: string[];
      };
    }[];
    available_days?: string[];
    total_available_days?: number;
  };
  premeal_info?: {
    meal_type?: string;
    serving_time?: string;
    cutoff_time?: string;
    today?: {
      date?: string;
      available?: boolean;
      reason?: string;
    };
    store_timings?: {
      breakfast_time?: string;
      lunch_time?: string;
      dinner_time?: string;
    };
    booking_rules?: {
      advance_booking_days?: number;
      max_booking_date?: string;
      daily_meal_limit?: number;
    };

  };

};

type MealProductDatesT = {
  date?: string,
  day_name?: string,
  cutoff_time?: string,
  serving_time?: string,
  items_string?: string,
  is_vegetarian?: boolean,
  meal_limit?: {
    available?: boolean,
    daily_limit?: number,
    used?: number,
    remaining?: number
  },
  subtotal?: number,
  company_contribution?: number,
  employee_share?: number
}

type IProductCheck = {
  product?: {
    id: number,
    name?: string,
    short_name?: string,
    thumbnail?: string,
    meal_type?: string,
    serving_time?: string
  },
  quantity?: number,
  unit_price?: number,
  dates?: MealProductDatesT[],
  summary?: {
    total_days?: number,
    gross_total?: number,
    total_company_contribution?: number,
    total_employee_share?: number,
    coupon_discount?: number,
    final_payable?: number,
    wallet_balance?: number
    total_tax?: number
  }
};


type ICartProduct = {
  cart_id?: number;
  product?: {
    id?: number;
    name?: string;
    short_name?: string;
    thumbnail?: string;
    is_vegetarian?: boolean;
    is_vegan?: boolean;
    is_in_stock?: boolean;
    available_stock?: null;
  };
  quantity?: number;
  unit_price?: number;
  tax_percentage?: number;
  item_subtotal?: number;
  item_tax?: number;
  item_total?: number;
  note?: string;
  //
  product_id?: number,
  product_name?: string,
  base_price?: number,
  tax_amount?: number,
  subtotal?: number,
  total?: number,
  is_in_stock?: boolean,
  available_stock?: null
  thumbnail?: string;
  is_vegetarian?: boolean;
};

type IDeliveryLocation = {
  id?: number,
  name?: string,
  short_name?: string,
  floor?: string,
  building?: string
}

type IDepartment = {
  id?: number,
  name?: string,
  code?: string
}


type ICartList = {
  store?: {
    id?: number;
    name?: string;
    short_name?: string;
    address?: string;
    phone?: string;
  };
  items?: ICartProduct[];
  summary?: {
    total_items?: number;
    total_taxable_value?: number;
    total_tax?: number;
    discount?: number;
    total_payable?: number;
    //
    unique_items?: number,
    subtotal?: number,
    gross_total?: number,
    company_contribution?: number,
    employee_share?: number,
    discount_amount?: number,
    employee_payable?: number,
    wallet_balance?: number,
    wallet_to_use?: number,
    online_payment_amount?: number,
    payment_required?: boolean
  };
  policy?: string | null,
  coupon?: {
    code?: string,
    is_valid?: boolean,
    message?: string
  },
  delivery_locations?: IDeliveryLocation[],
  departments?: IDepartment[]
  available_balance?: number;
  order_process?: boolean;
  order_process_msg?: string;
  insufficient_stock_products?: [];
};

type IKOTCartList = {
  store: {
    id: number,
    name: string,
    short_name: string
  },
  items: {
    // cart_id: number,
    product_id: number,
    product_name: string,
    // quantity: number,
    // unit_price: number,
    // tax_percentage: number,
    base_price: number,
    tax_amount: number,
    subtotal: number,
    total: number,
    // note: string,
    is_in_stock: boolean,
    available_stock: null
  }[],
  summary: {
    // total_items: number,
    unique_items: number,
    subtotal: number,
    // total_tax: number,
    gross_total: number,
    company_contribution: number,
    employee_share: number,
    discount_amount: number,
    employee_payable: number,
    wallet_balance: number,
    wallet_to_use: number,
    online_payment_amount: number,
    payment_required: boolean
  },
  // policy: null,
  coupon: {
    code: string,
    is_valid: boolean,
    message: string
  },
  delivery_locations: {
    id: number,
    name: string,
    short_name: string,
    floor: string,
    building: string
  }[],
  departments: {
    id: number,
    name: string,
    code: string
  }[]
};

type IOrderSummary = {
  store: {
    id: number;
    name: string;
    short_name: string;
  };
  items: ICartProduct[];
  summary: {
    total_items: number;
    subtotal: number;
    total_tax: number;
    amount_before_discount: number;
    discount_amount: number;
    total_after_discount: number;
    wallet_balance: number;
    wallet_to_use: number;
    online_payment_amount: number;
  };
  coupon: {
    id?: number;
    code?: string;
    discount_type?: string;
    discount_value?: number;
  };
  razorpay: {
    key: string;
    amount: number;
    currency: string;
    name: string;
    description: string;
    image: string;
    order_id: string;
    prefill: {
      name: string;
      email: string;
      contact: string;
    };
    notes: {
      employee_id: string;
      store_id: string;
      module: string;
    };
    theme: {
      color: string;
    };
  };
  payment_required: boolean;
  pending_order_id: number;
};

type IPremealOrderSummary = {
  payment_required: boolean,
  pending_order_id: 104,
  razorpay: {
    key: string,
    amount: number,
    currency: string,
    name: string,
    description: string,
    order_id: string,
    prefill: {
      name: string,
      email: string,
      contact: string
    },
    theme: {
      color: string
    }
  },
  summary: {
    total_days: number,
    total_company_contribution: number,
    total_employee_share: number,
    coupon_discount: number,
    wallet_to_use: number,
    gateway_amount: number
  }
};


type OrderSuccessT = {
  order?: {
    id: number;
    order_number?: string;
    pickup_code?: string;
    status?: string;
    is_scheduled?: boolean;
    pickup_time?: string;
    total_amount?: number;
    wallet_deducted?: number;
    online_paid?: number;
    discount_amount?: number;
    items_count?: number;
    qr_data?: string;
    created_at?: string;
    module?: string;
    company_contribution?: number;
    employee_paid?: number;
  };
  delivery_location?: {
    id?: number,
    name?: string,
    short_name?: string,
    floor?: string,
    building?: string
  },
  store?: {
    id: number;
    name?: string;
    address?: string;
  };
  data?: {
    primary_order_id?: number,
    orders?: [
      {
        order_id: number,
        order_number?: string,
        scheduled_date?: string,
        meal_type?: string,
        pickup_time?: string,
        pickup_code?: string,
        qr_data?: string,
        status?: string,
        is_primary?: boolean,
        parent_order_id?: number | null,
        total_amount?: number,
        company_contribution?: number,
        employee_paid?: number
      }
    ],
    payment_summary?: {
      total_company_contribution?: number,
      total_employee_paid?: number,
      wallet_used?: number,
      gateway_paid?: number,
      coupon_discount?: number,
      payment_status?: string
    },
    wallet_balance_after?: number
  }
};



type CouponT = {
  valid?: boolean;
  coupon?: {
    id?: number;
    code?: string;
    name?: string;
    discount_type?: string;
    discount_value?: number;
    max_discount_amount?: number;
    min_order_amount?: number;
  };
  pricing?: {
    original_amount?: number;
    discount_amount?: number;
    total_after_discount?: number;
  };
  message?: string;
};

type ProfileDataT = {
  id?: number;
  first_name?: string;
  last_name?: string;
  full_name?: string;
  email?: string;
  phone?: string;
  profile_picture?: string;
  wallet?: {
    available_balance?: number;
    formatted_balance?: string;
  };
};

type TransactionListT = {
  id?: number;
  uuid?: string;
  type?: 'CREDIT' | 'DEBIT';
  amount?: number;
  formatted_amount?: string;
  label?: string;
  date?: string;
  time?: string;
  formatted_date?: string;
  order?: any;
};

type TransactionT = {
  user?: {
    name?: string;
    profile_picture?: string;
  };
  wallet?: {
    available_balance?: number;
    total_credits?: number;
    total_debits?: number;
    formatted_balance?: string;
  };
  transactions?: TransactionListT[];
  pagination?: {
    current_page?: number;
    per_page?: number;
    total_count?: number;
    total_pages?: number;
    has_next?: boolean;
    has_previous?: boolean;
  };
};

type MiscDataT = {
  ios_version: number;
  ios_version_code: number;
  ios_version_name: string;
  ios_version_url: string;
  android_version: number;
  android_version_code: number;
  android_version_name: string;
  android_version_url: string;
  force_ios_update: boolean;
  force_android_update: boolean;
  privacy_policy: string;
  terms_and_conditions: string;
  refunds_cancellations_policy: string;
  about_us: string;
};

type OrderProductItemT = {
  id?: number;
  product_id?: number;
  name?: string;
  short_name?: string;
  thumbnail?: string;
  quantity?: number;
  price?: number;
  tax_amount?: number;
  total_price?: number;
  is_vegetarian?: boolean;
  notes?: string;
  formatted_scheduled_date?: string;
};

type OrderItemT = {
  id?: number;
  order_number?: string;
  module?: string;
  status?: string;
  status_label?: string;
  status_color?: string;
  total_amount?: number;
  formatted_amount?: string;
  items_count?: number;
  items?: OrderProductItemT[];
  store?: {
    name?: string;
    address?: string;
  };
  is_scheduled?: boolean;
  created_at?: string;
  formatted_date?: string;
  scheduled_date?: string;
  meal_type?: string;
  pickup_time?: string;
};

type OrderListT = {
  orders: OrderItemT[];
  pagination?: {
    current_page?: number;
    per_page?: number;
    total_count?: number;
    total_pages?: number;
    has_next?: boolean;
    has_previous?: boolean;
  };
};

type ScheduledOrderT = {
  id?: number;
  order_number?: string;
  status?: string;
  status_label?: string;
  status_color?: string;
  scheduled_date?: string;
  formatted_date?: string;
  day_name?: string;
  meal_type?: string;
  items_string?: string;
  is_vegetarian?: boolean;
  pickup_time?: string;
  pickup_code?: string;
  total_amount?: number;
  formatted_amount?: string;
  can_cancel?: boolean;
  cancel_reason?: string;
};

type BookingSummaryT = {
  total_days: number;
  items_total: number;
  company_contribution: number;
  employee_contribution: number;
  discount: number;
  wallet_deducted: number;
  amount_payable: number;
  formatted_items_total: string;
  formatted_company_contribution: string;
  formatted_employee_contribution: string;
  formatted_discount: string;
  formatted_wallet_deducted: string;
  formatted_amount_payable: string;
  formatted_tax: string;
  formatted_online_paid?: string;
}

type OrderDetailsT = {
  id?: number;
  order_number?: string;
  module?: string;
  status?: string;
  status_label?: string;
  status_color?: string;
  pickup_code?: string;
  store?: {
    name?: string;
    address?: string;
    phone?: string;
  };
  items?: OrderProductItemT[];
  items_count?: number;
  pickup?: {
    code?: string;
    qr_data?: string;
    ready_at?: string;
    formatted_ready_at?: string;
  };
  pricing?: {
    subtotal?: number;
    tax?: number;
    discount?: number;
    total?: number;
    wallet_deducted?: number;
    company_contribution?: number;
    employee_contribution?: number;
    formatted_subtotal?: string;
    formatted_tax?: string;
    formatted_discount?: string;
    formatted_total?: string;
    formatted_company_contribution?: string;
    formatted_employee_contribution?: string;
    formatted_wallet_deducted?: string;
    formatted_online_paid?: string;
  };
  payment?: {
    method?: string;
    status?: string;
  };
  cancellation?: {
    can_cancel?: boolean;
    reason?: string;
  };
  delivery_location?: {
    id?: number,
    name?: string,
    short_name?: string,
    floor?: string,
    building?: string
  },
  department?: {
    id?: number,
    name?: string,
    code?: string
  },
  is_scheduled?: boolean;
  pickup_time?: string;
  formatted_pickup_date_time?: string;
  created_at?: string;
  formatted_date?: string;
  scheduled_date?: string;
  formatted_scheduled_date?: string;
  collect_review?: boolean;
  meal_type?: string;
  booking_summary?: BookingSummaryT;
  scheduled_orders?: ScheduledOrderT[];
  statuses?: {
    code?: string;
    text?: string;
    is_completed?: boolean;
    is_current?: boolean;
  }[];


  order?: {
    id?: number,
    order_number?: string,
    module?: string,
    status?: string,
    status_label?: string,
    status_color?: string,
    pickup_code?: string,
    store?: {
      name?: string,
      address?: string,
      phone?: string
    },
    items?: [
      {
        id?: number,
        product_id?: number,
        name?: string,
        short_name?: string | null,
        thumbnail?: string,
        quantity?: number,
        price?: number,
        tax_amount?: number,
        total_price?: number,
        is_vegetarian?: boolean,
        notes?: string | null
      }
    ],
    items_count?: number,
    pickup?: {
      code?: string,
      qr_data?: string,
      ready_at?: string | null,
      formatted_ready_at?: string | null
    },
    pricing?: {
      subtotal?: number,
      tax?: number,
      discount?: number,
      total?: number,
      wallet_deducted?: number,
      company_contribution?: number,
      employee_contribution?: number,
      formatted_subtotal?: string,
      formatted_tax?: string,
      formatted_discount?: string,
      formatted_total?: string
    },
    payment?: {
      method?: string,
      status?: string
    },
    cancellation?: {
      can_cancel?: boolean,
      reason?: string | null
    },
    is_scheduled?: boolean,
    pickup_time?: string,
    formatted_pickup_date_time?: string,
    created_at?: string,
    formatted_date?: string,
    scheduled_date?: string,
    formatted_scheduled_date?: string,
    meal_type?: string
    scheduled_orders?: [
      {
        id?: number,
        order_number?: string,
        status?: string,
        status_label?: string,
        status_color?: string,
        scheduled_date?: string,
        formatted_date?: string,
        day_name?: string,
        meal_type?: string,
        pickup_time?: string,
        pickup_code?: string,
        total_amount?: number,
        formatted_amount?: string,
        can_cancel?: boolean,
        cancel_reason?: string | null
      },
      {
        id?: number,
        order_number?: string,
        status?: string,
        status_label?: string,
        status_color?: string,
        scheduled_date?: string,
        formatted_date?: string,
        day_name?: string,
        meal_type?: string,
        pickup_time?: string,
        pickup_code?: string,
        total_amount?: number,
        formatted_amount?: string,
        can_cancel?: boolean,
        cancel_reason?: string | null
      }
    ],
  },
}


type SupportContactT = {
  topics: string[];
  support_email: string;
  support_phone: string;
};

type NotificationsItem = {
  id: number,
  type?: string,
  title?: string,
  message?: string,
  order_id?: number,
  order_number?: string,
  module?: string,
  time_ago?: string,
  created_at?: string

}

type NotificationsT = {
  notifications: NotificationsItem[];
  pagination?: {
    current_page?: number,
    per_page?: number,
    total_count?: number,
    total_pages?: number,
    has_next?: boolean,
    has_previous?: boolean
  }
}

type ReorderT = {
  store_id?: number,
  module?: string,
  cart_items_count?: number,
  added?: [],
  updated?: [],
  skipped?: {
    product_id?: number,
    name?: string,
    cart_qty?: number,
    reorder_qty?: number
  }[],
  unavailable?: []
}



export type {
  Prettify,
  StackProps,
  NavProps,
  AppStackParamList,
  BottomTabParamList,
  IAppState,
  IAppCtx,
  UserType,
  ICompanyCode,
  ISnackbar,
  IUser,
  IBanner,
  CategoryListT,
  ProductListT,
  IProductDetail,
  IStoreData,
  ICartList,
  ICartProduct,
  IOrderSummary,
  OrderSuccessT,
  CouponT,
  ProfileDataT,
  TransactionT,
  MiscDataT,
  TransactionListT,
  OrderListT,
  OrderItemT,
  OrderProductItemT,
  OrderDetailsT,
  IProductCheck,
  IPremealOrderSummary,
  SupportContactT,
  ScheduledOrderT,
  NotificationsT,
  NotificationsItem,
  BookingSummaryT,
  IDeliveryLocation,
  IDepartment,
  MealProductDatesT,
  ReorderT
};
