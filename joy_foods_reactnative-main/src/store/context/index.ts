import { createContext } from "react";
import { initialCtx } from "../../utils";
import { IContext } from "./Types";

const AppCtx = createContext<IContext>({
    state: initialCtx,
    dispatch: () => {},
});

export default AppCtx;
export * from "./Actions";
