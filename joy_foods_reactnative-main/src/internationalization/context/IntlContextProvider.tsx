import React, { FC, ReactNode, useReducer } from "react";
import IntlContext, { intl_InitialState } from "./";
import intlReducer from "./intlReducer";

const IntlContextProvider: FC<{ children: ReactNode }> = ({ children }) => {
    const [state, dispatch] = useReducer(intlReducer, intl_InitialState);
    const val = { state, dispatch };
    return (
        <IntlContext.Provider value={val}>
            {children}
        </IntlContext.Provider>
    );
};

export default IntlContextProvider;