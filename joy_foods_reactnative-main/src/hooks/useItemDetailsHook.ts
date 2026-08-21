import { GETreq, POSTreq } from "api";
import { useLoader } from "components";
import { useAddMinusCart } from "hooks";
import { useT } from "internationalization";
import { useAtom } from "jotai";
import { useContext, useEffect, useRef, useState } from "react";
import { LayoutChangeEvent, ScrollView } from "react-native";
import { Extrapolation, interpolate, useAnimatedScrollHandler, useAnimatedStyle, useSharedValue } from "react-native-reanimated";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import { AppCtx, storeDataAtom } from "store";
import { SET_PRODUCT_DETAILS } from "store/context";
import { StackProps } from "types";
import StoreType from "types/StoreTypes";
import { _H, _WIDTH, isIOS } from "utils";

const IMAGE_HEIGHT = _H * 0.42;
const SCROLL_THRESHOLD = 150;

const useItemDetailsHook = ({ navigation, route: { params: { itemId, isFromMyCart } } }: StackProps<'ItemDetails'>) => {
    const scrollY = useSharedValue(0);
    const { t } = useT();
    const [page, setPage] = useState(0);
    const [storeData] = useAtom(storeDataAtom);
    const { handleAddToCart, handleIncrement, handleDecrement, alertRef } = useAddMinusCart();
    const { dispatch, state: { productDetails, productList, categoryList, cartData } } = useContext(AppCtx);
    const [cartItemCount, setCartItemCount] = useState(0);

    const { showLoader, hideLoader } = useLoader();

    const scrollRef = useRef<ScrollView>(null);
    const { top, bottom } = useSafeAreaInsets();
    const [expandedIds, setExpandedIds] = useState<number[]>([]);
    const [isBooking, setIsBooking] = useState(false);
    const [contentHeight, setContentHeight] = useState(0);
    const [error, setError] = useState<string>();

    const onScroll = (event: any) => {
        const currentPage = Math.round(event.nativeEvent.contentOffset.x / _WIDTH);
        setPage(currentPage);
    };



    const toggleExpand = (id: number) => {
        setExpandedIds(prev =>
            prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id],
        );
    };

    const scrollHandler = useAnimatedScrollHandler({
        onScroll: event => {
            scrollY.value = event.contentOffset.y;
        },
    });

    const imageAnimatedStyle = useAnimatedStyle(() => {
        const translateY = interpolate(
            scrollY.value,
            [0, IMAGE_HEIGHT],
            [0, -IMAGE_HEIGHT],
            Extrapolation.CLAMP,
        );

        const scale = interpolate(
            scrollY.value,
            [-IMAGE_HEIGHT, 0],
            [1.5, 1],
            Extrapolation.CLAMP,
        );

        const opacity = interpolate(
            scrollY.value,
            [0, IMAGE_HEIGHT],
            [1, 0],
            Extrapolation.CLAMP,
        );

        return {
            transform: [{ translateY }, { scale }],
            opacity,
        };
    });

    const titleAnimatedStyle = useAnimatedStyle(() => {
        const startY = IMAGE_HEIGHT + 16;
        const endY = top + 20;
        const totalDistanceY = startY - endY;

        const translateY = interpolate(
            scrollY.value,
            [0, SCROLL_THRESHOLD],
            [0, -totalDistanceY],
            Extrapolation.CLAMP,
        );

        const translateX = interpolate(
            scrollY.value,
            [0, SCROLL_THRESHOLD],
            [0, isIOS ? 40 : 35],
            Extrapolation.CLAMP,
        );

        const scale = interpolate(
            scrollY.value,
            [0, SCROLL_THRESHOLD],
            [1, 0.75],
            Extrapolation.CLAMP,
        );

        return {
            transform: [{ translateY }, { translateX }, { scale }],
        };
    });

    const headerBgStyle = useAnimatedStyle(() => {
        const opacity = interpolate(
            scrollY.value,
            [50, 100],
            [0, 1],
            Extrapolation.CLAMP,
        );

        return { opacity };
    });

    const handleOrderNow = () => {
        try {
            if (!productDetails?.is_in_cart) {
                handleAddToCart(productDetails?.id?.toString() ?? '');
            }
            navigation.navigate('OrderSummary');
        } catch (error) {
            console.log(error);
        }
    };

    const getProductDetails = async () => {
        try {
            showLoader();
            const params = {
                store_id: storeData?.id,
                module: storeData?.store_type,
                product_id: itemId,
            };
            const { data, success } = await GETreq('catalog/product_detail', params);
            console.log('Product Details::', success, JSON.stringify(data, null, 3));
            if (success) {
                dispatch({
                    type: SET_PRODUCT_DETAILS,
                    productDetails: data?.data?.product,
                });
            }
        } catch (error) {
            console.log(error);
        } finally {
            hideLoader();
        }
    };

    useEffect(() => {
        getProductDetails();
    }, []);

    const handleLayout = (event: LayoutChangeEvent) => {
        const { height } = event.nativeEvent.layout;
        setContentHeight(height);
    };

    const handleScheduleCheck = async (date: string[]) => {
        try {
            const payload = {
                store_id: storeData?.id,
                product_id: productDetails?.id,
                scheduled_dates: JSON.stringify(date),
            };

            console.log('schedule check PAyload::', JSON.stringify(payload, null, 3));
            const { data, success } = await POSTreq('orders/premeal_check', payload, true);
            console.log('schedule check Response::', JSON.stringify(data, null, 3));
            if (!success) {
                setError(data?.message || t('ERROR_OCCURED'));
                return { data: null, success: false };
            }
            setError(undefined);
            navigation.navigate('OrderSummary', { premealCheckData: data.data });
            setIsBooking(false);
            return { data, success };
        } catch (error) {
            console.log('ERROR::', error);
            setError(t('ERROR_OCCURED'));
            return { data: null, success: false };
        }
    }
    const handlePremealOrderNow = async () => {
        try {
            const payload = {
                store_id: storeData?.id,
                product_id: productDetails?.id,
                scheduled_dates: JSON.stringify(Date.now().toLocaleString('en-CA')),
                // wallet_amount: 0,
            };
            console.log('schedule check ', JSON.stringify(payload, null, 3));
            const { data, success } = await POSTreq('orders/premeal_check', payload, true);
            console.log('schedule check Response::', JSON.stringify(data, null, 3));
            navigation.navigate('OrderSummary', { premealCheckData: data.data });
            setIsBooking(false);
            return { data, success };
        } catch (error) {
            console.log('ERROR::', error);
            return { data: null, success: false };
        }
    }


    const getCartItemCount = async () => {
        if (StoreType.QSR === storeData.store_type || StoreType.KOT === storeData.store_type) {
            try {
                const params = {
                    store_id: storeData?.id,
                    module: storeData?.store_type,
                }
                const { data, success } =
                    await GETreq('cart/count', params);
                if (success) {
                    setCartItemCount(data?.data?.count ?? 0);
                } else {
                    console.log('Failed to fetch cart item count');
                }
            } catch (error) {
                console.log(error);
            }
        }
    }

    useEffect(() => {
        getCartItemCount();
    }, [cartData, productList]);

    return ({
        productDetails, t, navigation, handleAddToCart, handleOrderNow, handleScheduleCheck, handlePremealOrderNow,
        isBooking, setIsBooking, contentHeight, handleLayout, scrollHandler, imageAnimatedStyle, titleAnimatedStyle,
        headerBgStyle, page, onScroll, expandedIds, toggleExpand, storeData, IMAGE_HEIGHT, handleDecrement,
        handleIncrement, scrollRef, top, isFromMyCart, bottom, error, cartItemCount, alertRef
    });
}

export default useItemDetailsHook;
