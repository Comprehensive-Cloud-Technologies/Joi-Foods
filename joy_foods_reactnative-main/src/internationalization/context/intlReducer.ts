import { SET_LOADING, RESET_INTL, SET_TRANSLATION_OBJ } from './intlAction';
import { IAction, IIntlState } from './types';
import { intl_InitialState } from '.';
import { Prettify } from 'types';

function getValid(val: any, def: any) {
  return val === undefined ? def : val;
}

const intlReducer = (
  state: Prettify<IIntlState> = intl_InitialState,
  { type, isLoading, tObj }: IAction,
): IIntlState => {
  switch (type) {
    case SET_LOADING: {
      return { ...state, isLoading: getValid(isLoading, state.isLoading) };
    }
    case SET_TRANSLATION_OBJ: {
      return tObj ? { ...state, ...tObj } : state;
    }
    case RESET_INTL: {
      return { ...intl_InitialState };
    }
    default:
      return state;
  }
};

export default intlReducer;
