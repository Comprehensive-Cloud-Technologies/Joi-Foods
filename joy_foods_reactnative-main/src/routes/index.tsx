import { intl_InitialState, IntlContextProvider } from "internationalization";
import AppStackNav from "./AppStack";

function RouteNavigation() {

    return (
        <IntlContextProvider key={JSON.stringify(intl_InitialState).length}>
            <AppStackNav />
        </IntlContextProvider>
    )
};

export default RouteNavigation;