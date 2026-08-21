import { useCallback, useRef, useState } from 'react';
import { POSTreq } from 'api';
import { useLoader, useSnackbar } from 'components';
import { useT } from 'internationalization';
import { useAtom } from 'jotai';
import { storeDataAtom } from 'store/atoms';
import { IStoreData } from 'types';

const extractStoreCode = (raw?: string): string => {
  if (!raw) return '';
  const trimmed = String(raw).trim();
  if (!trimmed) return '';

  const match = trimmed.match(/\/store\/([^/?#]+)/i);
  if (match?.[1]) {
    return decodeURIComponent(match[1]);
  }

  return trimmed;
};

const useValidateStoreHook = () => {
  const { t } = useT();
  const { showLoader, hideLoader } = useLoader();
  const { showSnackbar } = useSnackbar();
  const [, setStoreData] = useAtom(storeDataAtom);
  const [isValidating, setIsValidating] = useState(false);
  const lastCodeRef = useRef<string>('');
  const inFlightRef = useRef(false);

  const validateStore = useCallback(
    async (rawCode?: string, options?: { silent?: boolean }) => {
      const storeCode = extractStoreCode(rawCode);
      console.log('Validating store Code:', storeCode);
      if (!storeCode) {
        if (!options?.silent) showSnackbar(t('STORE_NOT_FOUND'), 'error');
        return { success: false, storeCode: '', store: undefined as IStoreData | undefined };
      }

      if (inFlightRef.current && lastCodeRef.current === storeCode) {
        return { success: false, storeCode, store: undefined as IStoreData | undefined };
      }

      lastCodeRef.current = storeCode;
      inFlightRef.current = true;
      setIsValidating(true);
      if (!options?.silent) showLoader();

      try {
        const { success, data } = await POSTreq('stores/validate_store', { store_code: storeCode }, true);
        console.log('validate_store Response:', JSON.stringify(data, null, 3));
        let matchedStore: IStoreData | undefined;
        if (success) {
          matchedStore = data?.data?.store ?? data?.store ?? data?.data ?? undefined;
        } else {
          showSnackbar(data?.message ?? "Invalid Store")
        }

        if (matchedStore?.store_code) {
          setStoreData(matchedStore);
        }

        return { success: success, storeCode, store: matchedStore };
      } finally {
        if (!options?.silent) hideLoader();
        setIsValidating(false);
        inFlightRef.current = false;
      }
    },
    [hideLoader, setStoreData, showLoader, showSnackbar, t],
  );

  return { validateStore, isValidating, extractStoreCode };
};

export default useValidateStoreHook;
export { extractStoreCode };
