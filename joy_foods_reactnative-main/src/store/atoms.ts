import { useSetAtom } from "jotai";
import { initialCompanyCode, initialStoreData, initialUserData } from "utils";
import { atomWithMMKV } from "./mmkv";
import { ICompanyCode, IStoreData, IUser } from "types";

const isonboardingAtom = atomWithMMKV<boolean>("@isonboarding", false);
const companyCodeAtom = atomWithMMKV<ICompanyCode>("@companyCode", initialCompanyCode);
const userDataAtom = atomWithMMKV<IUser>("@userData", initialUserData);
const accessTokenAtom = atomWithMMKV<string>("@accessToken", "");
const storeDataAtom = atomWithMMKV<IStoreData>("@storeData", initialStoreData);
const searchHistoryAtom = atomWithMMKV<string[]>("@searchHistory", []);

const useResetAtoms = () => {
  const setUserData = useSetAtom(userDataAtom);
  const setAccessToken = useSetAtom(accessTokenAtom);
  const setStoreData = useSetAtom(storeDataAtom);
  const setSearchHistory = useSetAtom(searchHistoryAtom);

  const reset = () => {
    setUserData(initialUserData);
    setAccessToken("");
    setStoreData(initialStoreData);
    setSearchHistory([]);
  };

  return reset;
};

export { 
  isonboardingAtom, 
  companyCodeAtom, 
  userDataAtom, 
  accessTokenAtom, 
  storeDataAtom, 
  searchHistoryAtom,
  useResetAtoms
};
