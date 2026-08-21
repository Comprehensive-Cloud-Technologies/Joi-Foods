import { createContext } from "react";
import enJSON from "../translations/en.json";
import { IIntlContext, IIntlState } from "./types";
// import IntlContextProvider from "./IntlContextProvider";

const localJSON = { "en": enJSON };

const intl_InitialState: IIntlState = {
    "id": "0",
    "isLoading": true,
    "TranslationObj": localJSON?.["en"] || enJSON,
    "langCode": "en"
};

const IntlContext = createContext<IIntlContext>({
    state: intl_InitialState,
    dispatch: () => { }
});

export default IntlContext;
export * from "./intlAction";
export { intl_InitialState, localJSON };