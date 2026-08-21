import { Dispatch } from "react";
import { IAction } from "./types";

enum ActionType {
    SET_LOADING = "SET_LOADING",
    RESET_INTL = "RESET_INTL",
    SET_TRANSLATION_OBJ = "SET_TRANSLATION_OBJ"
};

const { SET_LOADING, RESET_INTL, SET_TRANSLATION_OBJ } = ActionType;

const set_Loading = (dispatch: Dispatch<IAction>, isLoading: boolean) => dispatch({ type: SET_LOADING, isLoading });
const set_TranslationObj = (dispatch: Dispatch<IAction>, tObj: IAction["tObj"]) => dispatch({ type: SET_TRANSLATION_OBJ, tObj });
const reset_Intl = (dispatch: Dispatch<IAction>) => dispatch({ type: RESET_INTL });

export {
    ActionType,
    set_Loading, reset_Intl, set_TranslationObj,
    SET_LOADING, RESET_INTL, SET_TRANSLATION_OBJ,
};