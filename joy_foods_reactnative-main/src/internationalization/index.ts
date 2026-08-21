import { useContext } from 'react';
import IntlCtx from './context';
import enJSON from './translations/en.json';
import IntlContextProvider from './context/IntlContextProvider';
import { intl_InitialState } from './context';

type tT = keyof typeof enJSON;

function useT() {
  const { state } = useContext(IntlCtx);
  const Translation = state?.TranslationObj;
  const lng = state?.langCode;

  function t(str: tT) {
    return Translation[str] || str;
  }

  function tEn(str: tT) {
    return enJSON[str] || str;
  }
  return { t, lng, tEn };
}

export { useT, IntlContextProvider, intl_InitialState };
export type { tT };
