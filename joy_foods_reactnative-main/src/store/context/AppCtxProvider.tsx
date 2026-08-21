import React, { FC, ReactNode, useReducer } from "react";
import intlReducer from "./Reducer";
import { initialCtx } from "../../utils";
import AppCtx from "./";

const AppCtxProvider: FC<{ children: ReactNode }> = ({ children }) => {
    const [state, dispatch] = useReducer(intlReducer, initialCtx);
    const val = { state, dispatch };

    return <AppCtx.Provider value={val}>{children}</AppCtx.Provider>;
};

export default AppCtxProvider;
