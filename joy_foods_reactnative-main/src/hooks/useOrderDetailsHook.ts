import { GETreq, POSTreq } from "api";
import { useLoader, useSnackbar } from "components";
import { decrementFormattedAmount } from "function";
import { useT } from "internationalization";
import { useAtom } from "jotai";
import { useContext, useEffect, useState } from "react";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import { AppCtx, storeDataAtom } from "store";
import { SET_ORDER_DATA, SET_PROFILE_DATA } from "store/context";
import { OrderDetailsT, StackProps } from "types";
import StoreType from "types/StoreTypes";

interface CancelOrderDays {
    itemID: number;
    scheduled_date: string;
    day: string;
    isCancelled?: boolean;
}

const useOrderDetailsHook = ({ navigation, route: { params: { orderId } } }: StackProps<"OrderDetailsScr">) => {

    const { top, bottom } = useSafeAreaInsets();
    const { t } = useT();
    const { dispatch, state: { orderData, profileData } } = useContext(AppCtx);
    const [isPickUpQR, setIsPickUpQR] = useState(false);
    const [orderDetails, setOrderDetails] = useState<OrderDetailsT>();
    const { showLoader, hideLoader } = useLoader();
    const [isCancelOrderSheetVisible, setIsCancelOrderSheetVisible] = useState(false);
    const [storeData] = useAtom(storeDataAtom);
    const [cancelOrderDays, setCancelOrderDays] = useState<CancelOrderDays[]>([]);
    const { showSnackbar } = useSnackbar();
    const [isCancel, setIsCancel] = useState(false);
    const [isFeedBack, setIsFeedBack] = useState(false);

    const getOrderDetails = async () => {
        try {
            showLoader();
            const { success, data } = await GETreq('my_orders/details', {
                order_id: orderId,
            });
            console.log('Order Details Response::', JSON.stringify(data, null, 3));
            if (success) {
                setOrderDetails(data?.data?.order);
                cancelOrderStatus(data?.data?.order);
            }
            hideLoader();
        } catch (error) {
            console.log(error);
            hideLoader();
        }
    };

    const cancelOrderStatus = (orderData: OrderDetailsT | undefined) => {
        if (orderData?.status === 'CANCELLED') {
            setIsCancel(false);
        } else {
            setIsCancel(true);
            return;
        }

        orderData?.scheduled_orders?.forEach((o: any) => {
            console.log('scheduled order status::', o.status);
            if (o.can_cancel) {
                setIsCancel(true);
                console.log('cancel true for cancelled can_cancel status');
                return;
            }
        })

        console.log('cancel false for non cancelled status');
    }

    useEffect(() => {
        getOrderDetails();
    }, []);



    const cancelOrder = async (reasonAndSelectedDays: string) => {
        try {
            setIsCancelOrderSheetVisible(false);
            showLoader();
            const endPoint = (StoreType.QSR === orderDetails?.module || StoreType.KOT === orderDetails?.module) ? 'my_orders/cancel' : 'my_orders/bulk_cancel';
            console.log('selected dates for cancellation::', reasonAndSelectedDays);
            const payload: any = {};
            if (StoreType.QSR === orderDetails?.module || StoreType.KOT === orderDetails?.module) {
                payload.order_id = orderId;
                payload.reason = reasonAndSelectedDays ? reasonAndSelectedDays : 'No reason provided';
            } else if (StoreType.PREMEAL === orderDetails?.module) {
                payload.order_ids = reasonAndSelectedDays
            }
            const { success, data } = await POSTreq(
                endPoint,
                payload,
                true,
            );
            console.log(
                'Cancel Order Response::',
                success,
                JSON.stringify(data, null, 3),
            );
            if (success) {
                getOrderDetails();
                showSnackbar(data.message);
                const updatedOrderData = orderData?.map(order => {
                    if (order.id == parseInt(orderId)) {
                        return {
                            ...order,
                            status: 'CANCELLED',
                            status_label: t('CANCELLED'),
                            status_color: '#F44336',
                        };
                    }
                    return order;
                });
                dispatch({
                    type: SET_ORDER_DATA,
                    orderData: updatedOrderData,
                });
                dispatch({
                    type: SET_PROFILE_DATA,
                    profileData: {
                        ...profileData,
                        wallet: {
                            ...profileData?.wallet,
                            available_balance: data?.data?.wallet?.available_balance,
                            formatted_balance: decrementFormattedAmount(
                                profileData?.wallet?.formatted_balance ?? '0',
                                orderDetails?.pricing?.total,
                                true,
                            ),
                        },
                    },
                });
            } else {
                showSnackbar(data?.message ?? t('ERROR_OCCURED'));
            }
            hideLoader();
        } catch (error) {
            console.log(error);
            hideLoader();
        }
    };

    const handelCancelOrder = () => {
        const cancelOrderDays: CancelOrderDays[] = []
        if (StoreType.PREMEAL === orderDetails?.module) {
            orderDetails?.scheduled_orders?.forEach((o: any) => {
                cancelOrderDays.push({
                    itemID: o.id ?? 0,
                    scheduled_date: o.scheduled_date?.slice(8, 10) ?? '',
                    day: new Date(o.scheduled_date ?? '').toLocaleDateString('en-US', { weekday: 'long' }).slice(0, 3),
                    isCancelled: o.status === 'CANCELLED' ? true : false
                })

            })
            setCancelOrderDays(cancelOrderDays);
        }

        // if (StoreType.QSR === orderDetails?.module || StoreType.KOT === orderDetails?.module) {
        orderDetails?.cancellation?.can_cancel && setIsCancelOrderSheetVisible(true);
        // } else if (StoreType.PREMEAL === orderDetails?.module) {
        //     setIsCancelOrderSheetVisible(true);
        // }
    }

    const handleReorder = async () => {
        try {
            showLoader();
            const payload = { order_id: orderId }
            const { success, data } = await POSTreq('my_orders/reorder', payload, true);
            console.log('Reorder Response::', JSON.stringify(data, null, 3));
            if (success) {
                navigation.navigate('OrderSummary', { reorderData: data?.data });
            } else {
                showSnackbar(data?.message ?? t('ERROR_OCCURED'));
            }
            hideLoader();
        } catch (err) {
            console.log("ERROR::", err);
        }
    }

    const handleFeedbackSuccess = () => {
        if (orderDetails) {
            setOrderDetails({
                ...orderDetails,
                collect_review: false,
            });
        }
    };

    return ({
        orderDetails, navigation, t, top, bottom, cancelOrder, handelCancelOrder, isCancelOrderSheetVisible,
        cancelOrderDays, setIsPickUpQR, isCancel, isPickUpQR, setIsCancelOrderSheetVisible, handleReorder,
        isFeedBack, setIsFeedBack, handleFeedbackSuccess
    });
}

export default useOrderDetailsHook;