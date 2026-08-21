import { useFocusEffect } from "@react-navigation/native";
import { GETreq, POSTreq } from "api";
import { DynamicInputRef, useLoader, useSnackbar } from "components";
import { decrementFormattedAmount } from "function";
import { useAddMinusCart } from "hooks";
import { useT } from "internationalization";
import { useAtom } from "jotai";
import { useCallback, useContext, useRef, useState } from "react";
import RazorpayCheckout, { SuccessResponse } from "react-native-razorpay";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import { AppCtx, storeDataAtom } from "store";
import { SET_CART_DATA, SET_PRODUCT_DETAILS, SET_PRODUCT_LIST, SET_PROFILE_DATA } from "store/context";
import { CouponT, ICartList, IDeliveryLocation, IDepartment, IOrderSummary, IPremealOrderSummary, IProductCheck, StackProps } from "types";
import StoreType from "types/StoreTypes";
import { isIOS } from "utils";

const useOrderSummaryHook = ({ navigation, route: { params } }: StackProps<'OrderSummary'>) => {
    const insets = useSafeAreaInsets();
    const { t } = useT();
    const [isWalletActive, setisWalletActive] = useState(false);
    const [isCoupon, setIsCoupon] = useState(false);
    const [couponCodeInput, setCouponCodeInput] = useState('');
    const [walletAmount, setWalletAmount] = useState('');
    const [isSchedule, setIsSchedule] = useState(false);
    const [isSchedulePickUpNow, setIsSchedulePickUpNow] = useState(true);
    const [isSchedulePickUpLater, setIsSchedulePickUpLater] = useState(false);
    const [isTimePickerVisible, setIsTimePickerVisible] = useState(false);
    const [selectedTime, setSelectedTime] = useState<Date | null>(null);
    const [isLoading, setIsLoading] = useState(false);
    const [couponData, setCouponData] = useState<CouponT>();
    const [delLocation, setDelLocation] = useState<IDeliveryLocation>({});
    const [department, setDepartment] = useState<IDepartment>({});
    const timeRef = useRef<DynamicInputRef>(null);

    const { handleIncrement, handleDecrement, alertRef } = useAddMinusCart();

    const [storeData] = useAtom(storeDataAtom);
    const [isSelectedDays, setIsSelectedDays] = useState(false);
    const [cartData, setCartData] = useState<ICartList>();
    const { showLoader, hideLoader } = useLoader();
    const { showSnackbar } = useSnackbar();
    const [error, setError] = useState<string | undefined>(undefined);
    const { dispatch, state: { productList, productDetails, profileData } } = useContext(AppCtx);
    const storeType = params?.reorderData?.module || storeData.store_type;
    const storeID = params?.reorderData?.store_id || storeData.id;

    const getCartData = async () => {
        if (storeType !== StoreType.PREMEAL) {
            try {
                showLoader();
                const payload = {
                    store_id: storeID,
                    module: storeType,
                };
                console.log('Get Cart List Payload::', JSON.stringify(payload, null, 3));
                const { data, success } = await GETreq('cart/list', payload);
                console.log('Get Cart List::', JSON.stringify(data, null, 3));
                if (success) {
                    if (storeType === StoreType.QSR) {
                        setCartData(data?.data);
                    }
                    dispatch({
                        type: SET_CART_DATA,
                        cartData: data?.data,
                    });
                }
            } catch (error) {
                console.log(error);
            } finally {
                hideLoader();
            }
        }
    };

    const getKotCartData = async () => {
        if (storeType === StoreType.KOT) {
            try {
                showLoader();
                const payload = {
                    store_id: storeID,
                    coupon_code: couponData?.coupon?.code,
                };
                console.log('Get Kot Cart Payload::', JSON.stringify(payload, null, 3));
                const { data, success } = await POSTreq('orders/kot_checkout_summary', payload, true);
                console.log('Get Kot Cart List::', JSON.stringify(data, null, 3));
                if (success) {
                    setCartData(data?.data);
                }
            } catch (error) {
                console.log(error);
            } finally {
                hideLoader();
            }
        }
    };


    useFocusEffect(
        useCallback(() => {
            getKotCartData();
            getCartData();
        }, []),
    );

    const onOrderComplete = async ({
        orderData,
        razorpayRes,
    }: {
        orderData: IOrderSummary;
        razorpayRes?: SuccessResponse;
    }) => {
        try {
            const payload: any = {
                store_id: storeID,
                pending_order_id: orderData?.pending_order_id,
            };
            if (orderData?.coupon) {
                payload.coupon_code = orderData.coupon?.code;
            }
            if (orderData?.summary?.wallet_to_use) {
                payload.wallet_amount = orderData?.summary?.wallet_to_use;
            }
            if (selectedTime) {
                payload.pickup_time = selectedTime.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false,
                });
            }
            if (razorpayRes?.razorpay_order_id) {
                payload.razorpay_order_id = razorpayRes.razorpay_order_id;
            }
            if (razorpayRes?.razorpay_payment_id) {
                payload.razorpay_payment_id = razorpayRes.razorpay_payment_id;
            }
            if (razorpayRes?.razorpay_signature) {
                payload.razorpay_signature = razorpayRes.razorpay_signature;
            }
            if (delLocation?.id) {
                payload.delivery_location_id = delLocation?.id;
            }
            if (department?.id) {
                payload.department_id = department?.id;
            }
            console.log('QSR Complete Payload::', JSON.stringify(payload, null, 3));
            const { data, success, code } = await POSTreq(storeType === StoreType.QSR ? 'orders/qsr_complete' : 'orders/kot_complete', payload, true);
            console.log('QSR Complete::', code, JSON.stringify(data, null, 3));
            setIsSchedule(false);
            if (success && data?.data) {
                navigation.replace('OrderSuccessfull', { orderData: data?.data, storeType });
                dispatch({ type: SET_CART_DATA, cartData: {} });
                dispatch({
                    type: SET_PROFILE_DATA, profileData: {
                        ...profileData,
                        wallet: {
                            available_balance: (profileData?.wallet?.available_balance ?? 0) - data?.data?.order?.wallet_deducted,
                            formatted_balance: decrementFormattedAmount(profileData?.wallet?.formatted_balance ?? "0", data?.data?.order?.wallet_deducted),
                        }
                    }
                })
                const updatedProductList = productList?.map(product => {
                    if (
                        orderData?.items
                            ?.map(item => item.cart_id)
                            .includes(product?.cart_id)
                    ) {
                        return {
                            ...product,
                            cart_quantity: 0,
                            is_in_cart: false,
                        };
                    }
                    return product;
                });
                dispatch({ type: SET_PRODUCT_LIST, productList: updatedProductList });
                dispatch({
                    type: SET_PRODUCT_DETAILS,
                    productDetails: {
                        ...productDetails,
                        cart_quantity: 0,
                        is_in_cart: false,
                    },
                });
            } else {
                showSnackbar(data?.message, 'error');
            }
        } catch (e) {
            console.log(e);
        }
    };

    const onPremealOrderComplete = async ({
        orderData,
        razorpayRes,
    }: {
        orderData: IPremealOrderSummary;
        razorpayRes?: SuccessResponse;
    }) => {
        try {
            const payload: any = {
                store_id: storeID,
                pending_order_id: orderData?.pending_order_id,
            };
            if (razorpayRes?.razorpay_order_id) {
                payload.razorpay_order_id = razorpayRes.razorpay_order_id;
            }
            if (razorpayRes?.razorpay_payment_id) {
                payload.razorpay_payment_id = razorpayRes.razorpay_payment_id;
            }
            if (razorpayRes?.razorpay_signature) {
                payload.razorpay_signature = razorpayRes.razorpay_signature;
            }
            if (orderData?.razorpay.order_id) {
                payload.order_id = orderData?.razorpay.order_id;
            }
            const { data, success } = await POSTreq('orders/premeal_complete', payload, true);
            console.log('Premeal Complete::', JSON.stringify(data, null, 3));
            setIsSchedule(false);
            if (success && data?.data) {
                navigation.replace('OrderSuccessfull', { orderData: data, storeType });
                dispatch({
                    type: SET_PROFILE_DATA, profileData: {
                        ...profileData,
                        wallet: {
                            available_balance: (profileData?.wallet?.available_balance ?? 0) - data?.data?.order?.wallet_deducted,
                            formatted_balance: decrementFormattedAmount(profileData?.wallet?.formatted_balance ?? "0", data?.data?.order?.wallet_deducted),
                        }
                    }
                })
            } else {
                showSnackbar(data?.message, 'error');
            }
        } catch (e) {
            console.log(e);
        }
    };

    const handleConfirm = async () => {
        try {
            if (isSchedulePickUpLater && !selectedTime) {
                timeRef.current?.setError(true, 'Please select time');
                return;
            }
            setIsSchedule(false);
            showLoader();
            const payload: any = {
                store_id: storeID,
            };
            if (couponData?.coupon?.code && couponData?.valid) {
                payload.coupon_code = couponData?.coupon?.code;
            }
            if (parseInt(walletAmount) > 0) {
                payload.wallet_amount = walletAmount;
            }
            if (selectedTime) {
                payload.pickup_time = selectedTime.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false,
                });
            }
            if (delLocation?.id) {
                payload.delivery_location_id = delLocation.id;
            }
            if (department?.id) {
                payload.department_id = department.id;
            }
            console.log('Payload::', JSON.stringify(payload, null, 3));

            const { data, success } = await POSTreq(storeType === StoreType.QSR ? 'orders/qsr_initiate' : 'orders/kot_initiate', payload, true);
            console.log('Confirm Cart::', success, JSON.stringify(data, null, 3));
            if (success) {
                setIsSchedule(false);
                hideLoader();
                const options = {
                    description: data?.data?.razorpay?.description,
                    image: data?.data?.razorpay?.image,
                    currency: data?.data?.razorpay?.currency,
                    key: data?.data?.razorpay?.key,
                    amount: data?.data?.razorpay?.amount,
                    name: data?.data?.razorpay?.name,
                    order_id: data?.data?.razorpay?.order_id,
                    prefill: {
                        email: data?.data?.razorpay?.prefill?.email,
                        contact: data?.data?.razorpay?.prefill?.contact,
                        name: data?.data?.razorpay?.prefill?.name,
                    },
                    theme: { color: data?.data?.razorpay?.theme?.color },
                };
                if (data?.data?.payment_required) {
                    console.log('Options::', JSON.stringify(options, null, 3));
                    RazorpayCheckout.open(options)
                        .then(DATA => {
                            console.log('Success:', DATA);
                            onOrderComplete({ orderData: data?.data, razorpayRes: DATA });
                        })
                        .catch(error => {
                            showSnackbar(error?.code == 0 ? "Payment processing cancelled by user" : error?.description, 'error');
                            console.log('Error:', error);
                        });
                } else {
                    onOrderComplete({ orderData: data?.data });
                }
            } else {
                hideLoader();
                showSnackbar(data?.message, 'error');
            }
        } catch (e) {
            console.log(e);
        }
    };

    const handlePremealPlaceOrder = async () => {
        try {
            showLoader();
            const payload: any = {
                store_id: storeID,
                product_id: productDetails?.id,
                scheduled_dates: JSON.stringify(productCheckDetails?.dates?.map(d => d.date)),
            };
            if (parseInt(walletAmount) > 0) {
                payload.wallet_amount = walletAmount;
            }
            if (couponData?.coupon?.code && couponData?.valid) {
                payload.coupon_code = couponData?.coupon?.code;
            }
            console.log('Payload::', JSON.stringify(payload, null, 3));

            const { data, success } = await POSTreq('orders/premeal_order', payload, true);
            console.log('Confirm Data ::', success, JSON.stringify(data, null, 3));
            if (success) {
                hideLoader();
                const options = {
                    description: data?.data?.razorpay?.description,
                    image: data?.data?.razorpay?.image,
                    currency: data?.data?.razorpay?.currency,
                    key: data?.data?.razorpay?.key,
                    amount: data?.data?.razorpay?.amount,
                    name: data?.data?.razorpay?.name,
                    order_id: data?.data?.razorpay?.order_id,
                    prefill: {
                        email: data?.data?.razorpay?.prefill?.email,
                        contact: data?.data?.razorpay?.prefill?.contact,
                        name: data?.data?.razorpay?.prefill?.name,
                    },
                    theme: { color: data?.data?.razorpay?.theme?.color },
                };
                if (data?.data?.payment_required) {
                    console.log('Options::', JSON.stringify(options, null, 3));
                    RazorpayCheckout.open(options)
                        .then(DATA => {
                            console.log('Success:', DATA);
                            console.log('Premeal Order Data::', JSON.stringify(data?.data, null, 3));
                            onPremealOrderComplete({ orderData: data?.data, razorpayRes: DATA });
                        })
                        .catch(error => {
                            showSnackbar(error?.code == 0 ? "Payment processing cancelled by user" : error?.description, 'error');
                            console.log('Error:', error);
                        });
                } else {
                    navigation.replace('OrderSuccessfull', { orderData: data, storeType });
                    // onPremealOrderComplete({ orderData: data?.data });
                }
            } else {
                hideLoader();
                showSnackbar(data?.message, 'error');
            }
        } catch (e) {
            console.log(e);
        }
    };

    const applyCouponCode = async () => {
        const amount = storeType === StoreType.QSR ? cartData?.summary?.total_payable : storeType === StoreType.KOT ? cartData?.summary?.employee_payable : productCheckDetails?.summary?.final_payable;
        try {
            setIsLoading(true);
            const payload = {
                store_id: storeID,
                coupon_code: couponCodeInput,
                module: storeType,
                amount: amount,
            };
            const { data, success } = await POSTreq('coupons/verify', payload, true);
            console.log('Coupon Verify::', JSON.stringify(data, null, 3));
            if (success && data?.data?.valid) {
                setCouponCodeInput('');
                setCouponData(data?.data);
            } else {
                showSnackbar(data?.message);
            }
        } catch (error) {
            console.log(error);
        } finally {
            setIsLoading(false);
        }
    };

    const onTimeChange = (event: any, date?: Date) => {
        if (isIOS) {
            if (date) {
                setSelectedTime(date);
            }
            if (event.type === 'set') {
                setIsTimePickerVisible(false);
            }
        } else {
            setIsTimePickerVisible(false);
            if (date) {
                setSelectedTime(date);
            }
        }
    };

    const [productCheckDetails, setProductCheckDetails] = useState<IProductCheck | undefined>(params?.premealCheckData);

    const handleScheduleCheck = async (date: string[]) => {
        try {
            const payload = {
                store_id: storeID,
                product_id: productDetails?.id,
                scheduled_dates: JSON.stringify(date),
                // wallet_amount: 0,
            };
            console.log('schedule check ', JSON.stringify(payload, null, 3));
            const { data, success } = await POSTreq('orders/premeal_check', payload, true);
            if (!success) {
                setIsSelectedDays(false);
                setError(data?.message || t('ERROR_OCCURED'));
                return { data: null, success: false };
            }
            console.log('schedule check Response::', JSON.stringify(data, null, 3));
            setError(undefined);
            setProductCheckDetails(data.data);
            setIsSelectedDays(false);
            return { data, success };
        } catch (error) {
            console.log('ERROR::', error);
            setError(t('ERROR_OCCURED'));
            return { data: null, success: false };
        }
    }

    return ({
        handleIncrement, getCartData, t, navigation, showSnackbar, handleDecrement, applyCouponCode, handlePremealPlaceOrder,
        walletAmount, setWalletAmount, couponData, setCouponData, couponCodeInput, setCouponCodeInput, isLoading, isTimePickerVisible,
        setIsTimePickerVisible, selectedTime, setSelectedTime, onTimeChange, productCheckDetails, setProductCheckDetails, storeData,
        cartData, setIsSchedule, isSchedule, productDetails, setIsSelectedDays, setError, insets, setIsCoupon, isCoupon, isWalletActive,
        setisWalletActive, setIsSchedulePickUpNow, setIsSchedulePickUpLater, isSchedulePickUpNow, isSchedulePickUpLater, timeRef,
        handleConfirm, isSelectedDays, error, handleScheduleCheck, alertRef, getKotCartData, storeType,
        delLocation, setDelLocation, department, setDepartment
    });
}

export default useOrderSummaryHook;