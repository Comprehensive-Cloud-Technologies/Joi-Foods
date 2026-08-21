import {
  BottomTabNavigationOptions,
  createBottomTabNavigator,
} from '@react-navigation/bottom-tabs';
import { createStackNavigator } from '@react-navigation/stack';
import { FontS } from 'function';
import {
  Dimensions,
  KeyboardAvoidingViewProps,
  Platform,
  StatusBar,
  StyleSheet,
} from 'react-native';
import {
  AppStackParamList,
  BottomTabParamList,
  IAppCtx,
  IAppState,
  ICompanyCode,
  IStoreData,
  IUser,
} from 'types';

const AppStack = createStackNavigator<AppStackParamList>();
const Tab = createBottomTabNavigator<BottomTabParamList>();

const isIOS = Platform.OS === 'ios';
const isAndroid = Platform.OS === 'android';
const _WIDTH = Dimensions.get('window').width;
const _W = Dimensions.get('screen').width;
const _HEIGHT = Dimensions.get('window').height;
const _H = Dimensions.get('screen').height;

const sbH = StatusBar?.currentHeight || 0;
const hLine = StyleSheet.hairlineWidth;
const kAvoidSty: KeyboardAvoidingViewProps['behavior'] =
  Platform.OS === 'ios' ? 'padding' : undefined;

const EmailRegex: RegExp =
  /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

const BottomTabOpt = (bottom: number): BottomTabNavigationOptions => ({
  headerShown: false,
  tabBarAllowFontScaling: false,
  tabBarStyle: {
    backgroundColor: _COL.WHITE,
    height: FontS(70) + bottom,
    alignItems: 'center',
    shadowColor: _COL.TRANSPARENT,
    elevation: 0,
    borderWidth: 0,
    shadowOffset: { width: 0, height: 0 },
    shadowOpacity: 0,
    shadowRadius: 0,
    borderTopWidth: 0,
    borderTopColor: _COL.TRANSPARENT,
  },
});

const _COL = {
  PRIMARY: '#2F94A4',
  PRIMARY_DARK: '#235F6D',
  SECONDARY: '#F19D20',
  SECONDARY_ORANGE: '#F69008',
  SECONDARY_DARK: '#EB6914',
  HEADER: '#23606E',
  HEADER_60: '#23606E99',
  TEXT_BLACK: '#4C4B4B',
  TEXT_BLACK_DARK: '#1A1A1A',
  FINAL_BLACK: '#1A1A1A',
  TEXT_GREY_LIGHT: '#8F8F8F',
  TEXT_GREY_MEDIUM: '#010101ff',
  TEXT_GREY: '#888888',
  TEXT_GREY50: '#02020280',
  PRIMARY_BG: '#EAF1EF80',
  CHECK_BOX: '#404040',
  BG: '#FFFFFF',
  WHITE: '#FFFFFF',
  WHITE_0: '#FFFFFF00',
  LAYOUT_BG: '#8484841A',
  SECOND_LAYOUT_BG: '#FFF7F7',
  SECONDARY_BG: '#FFF8F8',
  FORTH_BG: '#F9F8F8',
  THIRD_BG: '#FFF9F9',
  LIGHT_BG: '#FAFAFA',
  QR_BG: '#F7E7E7',
  BLACK: '#23331D',
  MAIN_BLACK: '#4C4B4B',
  GREY: '#C4C4C4',
  BORDER: '#E9E9E9',
  BORDER_SECOND: '#F4F8F9',
  BORDER_THIRD: '#EDEDED',
  BORDER_FOURTH: '#ECECEC',
  BORDER_FIFTH: '#E0E0E0',
  BORDER_SIXTH: '#E7E7E7',
  BORDER_SEVENTH: '#F7F7F7',
  BORDER_EIGHTH: '#F0F0F0',
  BORDER_NINTH: '#F5F5F5',
  BORDER_TENTH: '#F8E3E3',
  INPUT_BG: '#EAF1EF',
  INPUT_BORDER: '#C7D7D2',
  INTEREST_BG: '#F5F8F7',
  MYSTIC: '#DDE8E5',
  LIGHT_GREY: '#D9D9D9',
  DIVIDER: '#F2F0F0',
  LIGHT_BACKGROUND: '#E9E9E9',
  LIGHT_GREEN: '#CCFF99',
  LIGHT_RED: '#FFEFEE',
  GREEN: '#66BE70',
  SECONDARY_GREEN: '#6BAE2A',
  PRIMARY_RED: '#BD3839',
  SECONDARY_RED: '#BD383954',
  THIRD_RED: '#E63946',
  ERROR: '#BD3839',
  RED: '#F75F6A',
  BLUE: '#007bff',
  ERROR_RED: '#F57782',
  DARKER_RED: '#660000',
  LIGHT_GRAY: '#656565',
  SHADOW_COLOR: '#868686',
  TRACK_ONE: '#F1F1F1',
  TRACK_SECOND: '#FFE0E2',
  LINEARGRADIENT_START: '#DA5C61',
  LINEARGRADIENT_END: '#BF3B3D',
  LIGHT_PINK: '#BD9191',

  TRANSPARENT: 'transparent',
  SECONDARY_ORANGE_10: 'rgba(246, 144, 8, 0.1)',
  SECONDARY_GREEN_10: 'rgba(107, 174, 42, 0.1)',
  PRIMARY_RED_10: 'rgba(189, 56, 57, 0.1)',
  TEXT_GREY_10: 'rgba(136, 136, 136, 0.1)',
  DIMMED_BG: 'rgba(45, 43, 43, 1)0.72)',
  PRIMARY_DARK005: 'rgba(35, 95, 109, 0.05)',
  PRIMARY_DARK01: 'rgba(35, 95, 109, .1)',
  PRIMARY_DARK02: 'rgba(35, 95, 109, .2)',
  PRIMARY_DARK03: 'rgba(35, 95, 109, .3)',
  PRIMARY_DARK04: 'rgba(35, 95, 109, .4)',
  PRIMARY_DARK05: 'rgba(35, 95, 109, .5)',
  PRIMARY_DARK06: 'rgba(35, 95, 109, .6)',
  PRIMARY_DARK07: 'rgba(35, 95, 109, .7)',
  PRIMARY_DARK08: 'rgba(35, 95, 109, .8)',
  PRIMARY_DARK09: 'rgba(35, 95, 109, .9)',
  PRIMARY005: 'rgba(47, 148, 164, .05)',
  PRIMARY01: 'rgba(47, 148, 164, .1)',
  PRIMARY02: 'rgba(47, 148, 164, .2)',
  PRIMARY03: 'rgba(47, 148, 164, .3)',
  PRIMARY04: 'rgba(47, 148, 164, .4)',
  PRIMARY05: 'rgba(47, 148, 164, .5)',
  PRIMARY06: 'rgba(47, 148, 164, .6)',
  PRIMARY07: 'rgba(47, 148, 164, .7)',
  PRIMARY08: 'rgba(47, 148, 164, .8)',
  PRIMARY09: 'rgba(47, 148, 164, .9)',
  BLACK00: 'rgba(0,0,0,0)',
  BLACK001: 'rgba(0,0,0,.001)',
  BLACK005: 'rgba(0,0,0,.05)',
  BLACK01: 'rgba(0,0,0,.1)',
  BLACK02: 'rgba(0,0,0,.2)',
  BLACK03: 'rgba(0,0,0,.3)',
  BLACK04: 'rgba(0,0,0,.4)',
  BLACK05: 'rgba(0,0,0,.5)',
  BLACK06: 'rgba(0,0,0,.6)',
  BLACK07: 'rgba(0,0,0,.7)',
  BLACK08: 'rgba(0,0,0,.8)',
  BLACK09: 'rgba(0,0,0,.9)',
  WHITE00: 'rgba(256,256,256,0.0)',
  WHITE01: 'rgba(256,256,256,0.1)',
  WHITE015: 'rgba(256,256,256,0.15)',
  WHITE02: 'rgba(256,256,256,0.2)',
  WHITE025: 'rgba(256,256,256,0.25)',
  WHITE03: 'rgba(256,256,256,0.3)',
  WHITE04: 'rgba(256,256,256,0.4)',
  WHITE05: 'rgba(256,256,256,0.5)',
  WHITE06: 'rgba(256,256,256,0.6)',
  WHITE07: 'rgba(256,256,256,0.7)',
  WHITE08: 'rgba(256,256,256,0.8)',
  WHITE09: 'rgba(256,256,256,0.9)',
  PRIMARY_DARK_005: 'rgba(35, 95, 109,.05)',
  PRIMARY_DARK_01: 'rgba(35, 95, 109,.1)',
};

const FONT = {
  /* 900  */ BLACK: 'Poppins-Black',
  /* 900I */ BLACK_ITALIC: 'Poppins-BlackItalic',
  /* 700  */ BOLD: 'Poppins-Bold',
  /* 700I */ BOLD_ITALIC: 'Poppins-BoldItalic',
  /* 500  */ MEDIUM: 'Poppins-Medium',
  /* 500I */ MEDIUM_ITALIC: 'Poppins-MediumItalic',
  /* 400  */ REGULAR: 'Poppins-Regular',
  /* 400I */ ITALIC: 'Poppins-Italic',
  /* 300  */ LIGHT: 'Poppins-Light',
  /* 300I */ LIGHT_ITALIC: 'Poppins-LightItalic',
  /* 200  */ EXTRA_LIGHT: 'Poppins-ExtraLight',
  /* 200I */ EXTRA_LIGHT_ITALIC: 'Poppins-ExtraLightItalic',
  /* 100  */ THIN: 'Poppins-Thin',
  /* 100I */ THIN_ITALIC: 'Poppins-ThinItalic',
  /* 600  */ SEMI_BOLD: 'Poppins-SemiBold',
  /* 600I */ SEMI_BOLD_ITALIC: 'Poppins-SemiBoldItalic',
  /* 800  */ EXTRA_BOLD: 'Poppins-ExtraBold',
  /* 800I */ EXTRA_BOLD_ITALIC: 'Poppins-ExtraBoldItalic',
};

const initialState: IAppState = Object.freeze({
  user: {},
  token: '',
  next_step: '',
  interests: [],
  _logout: { loggingOut: false, showAlert: false },
  _prompt: null,
});

const initialCompanyCode: ICompanyCode = Object.freeze({
  id: '',
  company_name: '',
  company_code: '',
});

const initialStoreData: IStoreData = Object.freeze({
  id: 0,
  store_code: "",
  name: "",
  short_name: "",
  store_type: "",
  thumbnail: "",
  primary_email: "",
  primary_phone: "",
  address: {
    line1: "",
    city: "",
    state: ""
  },
  is_operational: false
})

const initialUserData: IUser = Object.freeze({
  id: '',
  employee_code: '',
  first_name: '',
  last_name: '',
  email: '',
  company_id: '',
  kot_permission: false,
  qsr_access: false,
  premeal_access: false,
});

const initialCtx: IAppCtx = Object.freeze({
  isSplashHide: false,
  isAuthPass: false,
  isConnected: true, // assume connected until NetInfo listener says otherwise
});

export {
  AppStack,
  Tab,
  BottomTabOpt,
  isAndroid,
  isIOS,
  _HEIGHT,
  _H,
  _WIDTH,
  _W,
  hLine,
  kAvoidSty,
  sbH,
  FONT,
  _COL,
  initialState,
  initialCtx,
  initialCompanyCode,
  initialUserData,
  EmailRegex,
  initialStoreData
};
