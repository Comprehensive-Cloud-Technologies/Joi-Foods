import { Dispatch } from 'react';
import { Prettify } from 'types';
import { ActionType } from './intlAction';
import enJSON from '../translations/en.json';

type langCodeT = 'en' | 'ar';

type tKeyType = keyof typeof enJSON;
type tType = typeof enJSON;

interface IIntlState {
  id: string;
  isLoading: boolean;
  TranslationObj: tType;
  langCode: Prettify<langCodeT>;
}

interface IIntlContext {
  state: IIntlState;
  dispatch: Dispatch<IAction>;
}

type IAction = {
  type: ActionType;
  isLoading?: boolean;
  tObj?: { TranslationObj: Prettify<tType>; langCode: Prettify<langCodeT> };
};

export type { IIntlState, IIntlContext, IAction, tKeyType, tType, langCodeT };
