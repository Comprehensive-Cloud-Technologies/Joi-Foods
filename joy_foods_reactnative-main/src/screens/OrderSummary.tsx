import { View, Text, TouchableOpacity, StyleSheet, ScrollView, FlatList, ActivityIndicator, Animated } from 'react-native';
import React, { useState, useRef, useEffect } from 'react';
import { _COL, _H, _W, _WIDTH, FONT, isIOS } from 'utils';
import BottomActionSheet from 'components/ui/BottomActionSheet';
import { BACK_BTN_IC, CLOCK2_IC, CLOCK_IC, COUPON_CODE_IC, DELETE_ICON, DOWN_IC, NON_VEG_IC, PENCIL_IC, VEG_IC } from 'assets';
import BTN from 'components/ui/BTN';
import { CartItemView, ConfirmationAlert, CustomToggleSwitch, Dropdown, InputField, ScheduleCalendarSheet } from 'components';
import { ICartProduct, IDeliveryLocation, IDepartment, MealProductDatesT, StackProps } from 'types';
import RNDateTimePicker from '@react-native-community/datetimepicker';
import { useOrderSummary } from 'hooks';
import StoreType from 'types/StoreTypes';
import { KeyboardAvoidingView } from 'react-native-keyboard-controller'
import { orderSummarySty } from 'styles';
import DashedLine from 'components/DashedLine';
import TextButton from 'components/TextButton';

const OrderSummary = (Props: StackProps<'OrderSummary'>) => {

  const {
    handleIncrement, getCartData, t, navigation, handleDecrement, showSnackbar, applyCouponCode, couponCodeInput, handlePremealPlaceOrder,
    couponData, isLoading, isTimePickerVisible, onTimeChange, productCheckDetails, selectedTime, setCouponCodeInput, setCouponData,
    setIsTimePickerVisible, setSelectedTime, setWalletAmount, walletAmount, storeData, cartData, insets, isSchedule,
    productDetails, setIsSchedule, setIsSelectedDays, isCoupon, isWalletActive, setIsCoupon, setisWalletActive, isSchedulePickUpNow,
    isSchedulePickUpLater, timeRef, handleConfirm, isSelectedDays, error, handleScheduleCheck, alertRef, getKotCartData,
    delLocation, setDelLocation, department, setDepartment, setIsSchedulePickUpLater, setIsSchedulePickUpNow, storeType
  } = useOrderSummary(Props);


  const [isDelLocationVisible, setIsDelLocationVisible] = useState(false);
  const [isDepartmentVisible, setIsDepartmentVisible] = useState(false);



  const renderOrderItem = ({ item }: { item: ICartProduct }) => (
    <CartItemView
      item={item}
      handleQuantityPlus={() => {
        handleIncrement(item.cart_id?.toString() ?? '')?.then(() => {
          getCartData();
          getKotCartData();
        })
      }}
      handleQuantityMinus={(quantity) => {
        if (quantity == 1) {
          showSnackbar('You can not remove this item', 'error');
          return;
        }
        handleDecrement(item.cart_id?.toString() ?? '')?.then(() => {
          getCartData();
          getKotCartData();
        })
      }}
    />
  );

  const renderMealScheduleItem = ({ item, index }: { item?: MealProductDatesT; index: number }) => {
    const isLastItem = index === (productCheckDetails?.dates?.length ?? 0) - 1;
    const targetDate = new Date(item?.date ?? '');
    const formattedTarget = targetDate.toLocaleDateString('en-GB', {
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    });

    return (
      <View style={[orderSummarySty.mealScheduleItemContainer, isLastItem && { borderBottomWidth: 0, paddingBottom: 0 }]} key={item?.date?.toString()}>
        <View style={orderSummarySty.billInfoRow}>
          <Text allowFontScaling={false} style={orderSummarySty.mealScheduleItemDay}>{formattedTarget} {item?.day_name ?? ''}</Text>
          <Text allowFontScaling={false} style={orderSummarySty.mealScheduleItemPrice}>₹ {item?.subtotal?.toFixed(2)}</Text>
        </View>
        <View style={orderSummarySty.row}>
          <Text allowFontScaling={false} style={orderSummarySty.mealScheduleItems}>{item?.items_string}</Text>
          {!item?.is_vegetarian ? (<VEG_IC style={orderSummarySty.vegNonVegIcon} />) : (<NON_VEG_IC style={orderSummarySty.vegNonVegIcon} />)}
        </View>
      </View>
    );
  }

  const handlePlaceOrder = () => {
    const totalPayable =
      (StoreType.QSR === storeType || StoreType.KOT === storeType) ? (
        Number(cartData?.summary?.total_payable ?? cartData?.summary?.employee_payable ?? 0)) :
        StoreType.PREMEAL === storeType ? (
          (Number(productCheckDetails?.summary?.final_payable ?? 0))) : 0;
    const availableBalance =
      (StoreType.QSR === storeType || StoreType.KOT === storeType) ? (
        Number(cartData?.available_balance ?? cartData?.summary?.wallet_balance ?? 0)) :
        StoreType.PREMEAL === storeType ? (
          Number(productCheckDetails?.summary?.wallet_balance ?? 0)) : 0;
    const wallet = Number(walletAmount ?? 0);
    if (
      totalPayable < wallet ||
      availableBalance < wallet
    ) {
      showSnackbar('Please enter valid wallet amount');
      return;
    }
    StoreType.QSR === storeType && setIsSchedule(!isSchedule)

    if (StoreType.KOT === storeType) {
      if (!delLocation?.id) {
        showSnackbar('Please select delivery location');
        return;
      }
      if (!department?.id) {
        showSnackbar('Please select department');
        return;
      }
      handleConfirm();
      return;
    }

    StoreType.PREMEAL === storeType && handlePremealPlaceOrder();
  }

  return (
    <KeyboardAvoidingView
      behavior={'padding'}
      style={{ flex: 1 }}
    >
      <View style={[orderSummarySty.container, { paddingTop: insets.top + 12 }]}>
        <View style={{ flexDirection: 'row' }}>
          <TouchableOpacity
            style={orderSummarySty.backBtn}
            onPress={() => navigation.goBack()}
          >
            <BACK_BTN_IC />
          </TouchableOpacity>
          <Text allowFontScaling={false} style={orderSummarySty.title}>{t('ORDER_SUMMARY')}</Text>
        </View>
        <View style={orderSummarySty.divider} />

        <ScrollView
          showsVerticalScrollIndicator={false}
        >
          <View style={orderSummarySty.orderItemsContainer}>
            <View style={orderSummarySty.billInfoRow}>
              <Text allowFontScaling={false} style={[orderSummarySty.ordersHeading, { lineHeight: 24 }]}>
                {(StoreType.QSR === storeType || StoreType.KOT === storeType) ? t('ITEMS') : t('SELECTED_DAYS')}
              </Text>
              {StoreType.PREMEAL === storeType && (
                <TouchableOpacity
                  onPress={() => { setIsSelectedDays(true); }}>
                  <PENCIL_IC />
                </TouchableOpacity>
              )}
            </View>

            {(StoreType.QSR === storeType || StoreType.KOT === storeType) ? (
              <FlatList
                data={cartData?.items}
                keyExtractor={item => item.cart_id?.toString() ?? ''}
                renderItem={renderOrderItem}
                contentContainerStyle={[orderSummarySty.orderItemsContentContainer, { borderBottomWidth: StoreType.KOT === storeType ? 0 : 2, paddingBottom: StoreType.KOT === storeType ? 0 : 20 }]}
                scrollEnabled={false}
                ListFooterComponent={
                  <TouchableOpacity onPress={() => navigation.navigate('CategoryList')} activeOpacity={0.7}>
                    <Text style={{ fontFamily: FONT.MEDIUM, fontSize: 14, color: _COL.PRIMARY_RED, textDecorationLine: 'underline' }}>+Add More Items</Text>
                  </TouchableOpacity>
                }
              />
            ) : (
              <View>
                <FlatList
                  data={productCheckDetails?.dates}
                  horizontal
                  scrollEnabled={false}
                  showsHorizontalScrollIndicator={false}
                  renderItem={({ item, index }) => (
                    <View style={orderSummarySty.selectedDaysContainer} key={index}>
                      <Text allowFontScaling={false} style={orderSummarySty.selectedDaysText}>{item.day_name} {item.date?.slice(8, 10)}</Text>
                    </View>
                  )}
                  contentContainerStyle={{ gap: 6 }}
                />

                <View style={orderSummarySty.lunchTimeContainer}>
                  <CLOCK2_IC />
                  <Text allowFontScaling={false} style={orderSummarySty.lunchTimeText}>
                    {productCheckDetails?.product?.meal_type ?
                      (productCheckDetails?.product.meal_type.charAt(0).toUpperCase()
                        + productCheckDetails.product.meal_type.slice(1).toLowerCase()) : ''} timing : {productCheckDetails?.product?.serving_time}
                  </Text>
                </View>
                <View style={{ borderBottomColor: _COL.BORDER_NINTH, borderBottomWidth: 1, marginTop: 16 }} />
                <Text allowFontScaling={false} style={orderSummarySty.mealScheduleText}>{t('MEAL_SCHEDULE')}</Text>
                <FlatList
                  data={productCheckDetails?.dates}
                  keyExtractor={(item, index) => index.toString()}
                  scrollEnabled={false}
                  showsVerticalScrollIndicator={false}
                  renderItem={renderMealScheduleItem}
                />
                <DashedLine color={_COL.LIGHT_GRAY} />
              </View>
            )}
          </View>

          {StoreType.KOT == storeType
            ? (
              <View style={{ marginHorizontal: 16, borderTopWidth: 1, borderBottomWidth: 1, borderColor: _COL.BORDER, paddingBottom: 20 }}>
                <Dropdown
                  label="Delivery Location"
                  placeholder="Select Delivery Location"
                  value={delLocation?.name}
                  data={cartData?.delivery_locations || []}
                  visible={isDelLocationVisible}
                  onToggle={() => {
                    setIsDelLocationVisible(!isDelLocationVisible);
                    setIsDepartmentVisible(false);
                  }}
                  onSelect={setDelLocation}
                  onClose={() => setIsDelLocationVisible(false)}
                  zIndex={20}
                />

                <Dropdown
                  label="Department"
                  placeholder="Select Department"
                  value={department?.name}
                  data={cartData?.departments || []}
                  visible={isDepartmentVisible}
                  onToggle={() => {
                    setIsDepartmentVisible(!isDepartmentVisible);
                    setIsDelLocationVisible(false);
                  }}
                  onSelect={setDepartment}
                  onClose={() => setIsDepartmentVisible(false)}
                  zIndex={10}
                />
              </View>
            ) : (
              <View style={orderSummarySty.totalAmountContainer}>
                <Text allowFontScaling={false} style={orderSummarySty.totalAmountText}>{t('TOTAL_AMOUNT')}</Text>
                <Text allowFontScaling={false} style={orderSummarySty.totalAmountValue}>
                  {(StoreType.QSR === storeType) && (
                    `₹${cartData?.summary?.total_payable?.toFixed(2)}`
                  )}
                  {StoreType.PREMEAL === storeType && (
                    `₹${productCheckDetails?.summary?.gross_total?.toFixed(2)}`
                  )}
                </Text>
              </View>
            )}

          {StoreType.QSR === storeType && (
            (storeData?.address?.line1 ||
              storeData?.address?.city ||
              storeData?.address?.state) && (
              <View style={orderSummarySty.outletAddressContainer}>
                <Text allowFontScaling={false} style={orderSummarySty.ordersHeading}>{t('OUTLET_ADDRESS')}</Text>
                <Text allowFontScaling={false} style={orderSummarySty.outletAddressText}>
                  {storeData?.address?.line1} {storeData?.address?.city}{' '}
                  {storeData?.address?.state}
                </Text>
              </View>
            )
          )}

          <View style={orderSummarySty.billInfoContainer}>
            <Text allowFontScaling={false} style={orderSummarySty.ordersHeading}>{t('BILL_INFO')}</Text>
            <View style={orderSummarySty.billRowContainer}>
              <View style={orderSummarySty.billInfoRow}>
                <Text allowFontScaling={false} style={orderSummarySty.billInfoText}>{t('PRICE')}</Text>
                <Text allowFontScaling={false} style={orderSummarySty.billInfoAmount}>
                  {StoreType.QSR === storeType && (
                    `₹${cartData?.summary?.total_taxable_value?.toFixed(2)}`
                  )}
                  {StoreType.PREMEAL === storeType && (
                    `₹${productCheckDetails?.summary?.gross_total?.toFixed(2)}`
                  )}
                  {StoreType.KOT === storeType && (
                    `₹${cartData?.summary?.subtotal?.toFixed(2)}`
                  )}
                </Text>
              </View>
              {(cartData?.summary?.total_tax || productCheckDetails?.summary?.total_tax) && (
                <View style={orderSummarySty.billInfoRow}>
                  <Text allowFontScaling={false} style={orderSummarySty.billInfoText}>
                    {t('GST')}
                  </Text>
                  <Text allowFontScaling={false} style={orderSummarySty.billInfoAmount}>
                    {StoreType.PREMEAL === storeType ? (
                      `₹${productCheckDetails?.summary?.total_tax?.toFixed(2) ?? '00.00'}`
                    ) : (`₹${cartData?.summary?.total_tax?.toFixed(2) ?? '0.00'}`)}
                  </Text>
                </View>
              )}
              {(storeType === StoreType.KOT && (cartData?.summary?.gross_total ?? 0) > 0) && (
                <View style={orderSummarySty.billInfoRow}>
                  <Text allowFontScaling={false} style={orderSummarySty.billInfoText}>Gross total</Text>
                  <Text allowFontScaling={false} style={orderSummarySty.billInfoAmount}>
                    ₹{cartData?.summary?.gross_total?.toFixed(2)}
                  </Text>
                </View>
              )}
              {(productCheckDetails?.summary?.total_company_contribution || cartData?.summary?.company_contribution) && (
                <View style={orderSummarySty.billInfoRow}>
                  <Text allowFontScaling={false} style={orderSummarySty.billInfoText}>
                    {t('COMPANY_CONTRIBUTION')}
                  </Text>
                  <Text allowFontScaling={false} style={orderSummarySty.billInfoAmount}>
                    {StoreType.PREMEAL === storeType ? (
                      `₹${productCheckDetails?.summary?.total_company_contribution?.toFixed(2) ?? '0.00'}`
                    ) : (
                      `₹${cartData?.summary?.company_contribution?.toFixed(2) ?? '0.00'}`
                    )}
                  </Text>
                </View>
              )}
            </View>

            <View style={orderSummarySty.couponCodeContainer}>
              <View style={{ flexDirection: 'row' }}>
                <COUPON_CODE_IC />
                <Text allowFontScaling={false} style={orderSummarySty.couponCodeText}>
                  {!couponData?.valid
                    ? t('COUPON_CODE')
                    : couponData?.coupon?.code ?? couponCodeInput}
                </Text>
                {couponData?.valid && (
                  <TouchableOpacity
                    onPress={() => {
                      setIsCoupon(false);
                      setCouponData({});
                    }}
                  >
                    <DELETE_ICON style={orderSummarySty.deleteSVG} />
                  </TouchableOpacity>
                )}
              </View>

              {!isCoupon ? (
                <TouchableOpacity
                  onPress={() => {
                    setIsCoupon(!isCoupon);
                  }}
                >
                  <Text allowFontScaling={false} style={orderSummarySty.applyText}>{t('APPLY')}</Text>
                </TouchableOpacity>
              ) : !couponData?.valid ? (
                <InputField
                  placeholder="123456"
                  value={couponCodeInput}
                  onChangeText={setCouponCodeInput}
                  rightIcon={
                    <TouchableOpacity
                      onPress={applyCouponCode}
                      style={orderSummarySty.addBtn}
                    >
                      <Text allowFontScaling={false} style={orderSummarySty.addText}>{t('ADD')}</Text>
                    </TouchableOpacity>
                  }
                  containerStyle={{
                    width: '50%',
                    position: 'absolute',
                    right: 8,
                  }}
                  inputStyle={{ paddingVertical: 10, paddingHorizontal: 12 }}
                  rightIconSty={{ paddingHorizontal: 8 }}
                />
              ) : (
                <Text allowFontScaling={false} style={orderSummarySty.codeDiscount}>
                  - ₹{couponData?.pricing?.discount_amount?.toFixed(2)}
                </Text>
              )}
              {isLoading && (
                <View
                  style={{
                    ...StyleSheet.absoluteFill,
                    backgroundColor: _COL.WHITE04,
                    justifyContent: 'center',
                  }}
                >
                  <ActivityIndicator size="small" color={_COL.THIRD_RED} />
                </View>
              )}
            </View>

            <View style={orderSummarySty.billInfoTotalAmountContainer}>
              <Text allowFontScaling={false} style={orderSummarySty.billInfoTotalAmountText}>
                {t('TOTAL_AMOUNT')}
              </Text>
              <Text allowFontScaling={false} style={orderSummarySty.totalAmountValue}>
                ₹
                {(StoreType.QSR === storeType || StoreType.KOT === storeType) && (
                  couponData?.pricing?.total_after_discount ??
                  (cartData?.summary?.total_payable ?? cartData?.summary?.employee_payable)?.toFixed(2)
                )}
                {StoreType.PREMEAL === storeType && (
                  productCheckDetails?.summary?.final_payable?.toFixed(2) ?? '0.00'
                )}
              </Text>
            </View>

            <View style={orderSummarySty.walletBalanceContainer}>
              <View style={{ flexDirection: 'row' }}>
                <CustomToggleSwitch
                  value={isWalletActive}
                  onValueChange={value => {
                    if (StoreType.QSR === storeType || StoreType.KOT === storeType) {
                      if ((cartData?.available_balance ?? cartData?.summary?.wallet_balance) ?? 0 > 0) {
                        setWalletAmount(
                          value ?
                            ((cartData?.summary?.total_payable ?? cartData?.summary?.employee_payable ?? productCheckDetails?.summary?.total_employee_share ?? 0) > (cartData?.available_balance ?? cartData?.summary?.wallet_balance ?? 0) ? (cartData?.available_balance ?? cartData?.summary?.wallet_balance ?? 0) : (cartData?.summary?.total_payable ?? cartData?.summary?.employee_payable ?? productCheckDetails?.summary?.total_employee_share ?? 0))?.toFixed(2)
                            : ''
                        )
                        setisWalletActive(value);
                        // setWalletAmount('');
                      } else {
                        showSnackbar('Wallet balance is not available');
                      }
                    }
                    if (StoreType.PREMEAL === storeType) {
                      if (productCheckDetails?.summary?.wallet_balance ?? 0 > 0) {
                        setWalletAmount(
                          value ?
                            ((productCheckDetails?.summary?.total_employee_share ?? 0) > (productCheckDetails?.summary?.wallet_balance ?? 0) ? (productCheckDetails?.summary?.wallet_balance ?? 0) : (productCheckDetails?.summary?.total_employee_share ?? 0))?.toFixed(2)
                            : ''
                        )
                        setisWalletActive(value);
                      } else {
                        showSnackbar('Wallet balance is not available');
                      }
                    }
                  }}
                  activeColor={_COL.THIRD_RED + '33'}
                  inactiveColor={_COL.TRACK_ONE}
                  thumbColor={_COL.THIRD_RED}
                />
                <View style={{ marginLeft: 20 }}>
                  <Text allowFontScaling={false} style={orderSummarySty.walletBalanceText}>
                    {t('USE_WALLET_BALANCE')}
                  </Text>
                  <Text allowFontScaling={false} style={orderSummarySty.walletBalanceAmount}>
                    {t('AVAILABLE')}
                    {(StoreType.QSR === storeType || StoreType.KOT === storeType) && (
                      ` ₹${(cartData?.available_balance ?? cartData?.summary?.wallet_balance)?.toFixed(2)}`
                    )}
                    {StoreType.PREMEAL === storeType && (
                      ` ₹${productCheckDetails?.summary?.wallet_balance?.toFixed(2)}`
                    )}
                  </Text>
                </View>
              </View>

              <InputField
                onChangeText={txt => {
                  if (parseInt(txt) > (cartData?.summary?.total_payable ?? cartData?.summary?.employee_payable ?? productCheckDetails?.summary?.total_employee_share ?? 0)) {
                    return;
                  } else {
                    setWalletAmount(txt);
                  }
                }}
                value={walletAmount}
                placeholder="0.00"
                editable={isWalletActive}
                keyboardType="number-pad"
                containerStyle={{ width: '30%' }}
                inputStyle={{
                  textAlign: 'right',
                  paddingVertical: 12,
                  backgroundColor: _COL.WHITE,
                  paddingHorizontal: 12,
                }}
                inputContainerStyle={{
                  borderWidth: 2,
                  borderColor: _COL.BORDER_FIFTH,
                }}
              />
            </View>

            <View style={orderSummarySty.payAmountContainer}>
              <Text allowFontScaling={false} style={orderSummarySty.payAmountText}>
                {t('BALANCE_AMOUNT_TO_PAY')}
              </Text>
              <Text allowFontScaling={false} style={orderSummarySty.payAmountValue}>
                ₹
                {(StoreType.QSR === storeType || StoreType.KOT === storeType)
                  ? (
                    ((cartData?.summary?.total_payable ?? cartData?.summary?.employee_payable) ?? 0)
                    - (isWalletActive && walletAmount.length > 0 ? parseFloat(walletAmount) : 0)
                    - (couponData?.valid ? couponData?.pricing?.discount_amount ?? 0 : 0)
                  ).toFixed(2)
                  : StoreType.PREMEAL === storeType
                    ? (
                      (productCheckDetails?.summary?.final_payable ?? 0)
                      - (isWalletActive && walletAmount.length > 0 ? parseFloat(walletAmount) : 0)
                      - (couponData?.valid ? couponData?.pricing?.discount_amount ?? 0 : 0)
                    ).toFixed(2)
                    : null
                }
              </Text>
            </View>
          </View>
          <BTN
            title={t('PLACE_ORDER')}
            onP={handlePlaceOrder}
            width={_WIDTH - 40}
            ctr
            rounded
            borderR={50}
          />
          <TextButton
            onP={() => navigation.goBack()}
            isUnderline={true}
            style={{ paddingBottom: insets.bottom + 26, alignSelf: 'center', marginTop: 28 }}
            text={t('CANCEL')}
          />
        </ScrollView>

        <BottomActionSheet
          visible={isSchedule}
          onClose={() => setIsSchedule(false)}
          title={t('WHEN_DO_YOU_WANT_TO_PICKUP')}
          showCloseButton={false}
          showDivider={false}
          showHandle={true}
        >
          <View style={{ paddingHorizontal: 20, marginBottom: insets.bottom + 45 }}>
            <View style={orderSummarySty.schdulePickupContainer}>
              <TouchableOpacity
                onPress={() => {
                  setIsSchedulePickUpNow(true);
                  setIsSchedulePickUpLater(false);
                  setSelectedTime(null);
                  setIsTimePickerVisible(false);
                }}
                style={[
                  orderSummarySty.schedulePickupOptionNow,
                  isSchedulePickUpNow && orderSummarySty.schedulePickupOption,
                ]}
              >
                <Text allowFontScaling={false}
                  style={[
                    orderSummarySty.schedulePickupNow,
                    isSchedulePickUpNow && orderSummarySty.schedulePickupOptionText,
                  ]}
                >
                  {t('NOW')}
                </Text>
              </TouchableOpacity>
              <TouchableOpacity
                onPress={() => {
                  setIsSchedulePickUpLater(true);
                  setIsSchedulePickUpNow(false);
                }}
                style={[
                  orderSummarySty.schedulePickupOptionLater,
                  isSchedulePickUpLater && orderSummarySty.schedulePickupOption,
                ]}
              >
                <Text allowFontScaling={false}
                  style={[
                    orderSummarySty.schedulePickupLater,
                    isSchedulePickUpLater && orderSummarySty.schedulePickupOptionText,
                  ]}
                >
                  {t('SCHEDULE_LATER')}
                </Text>
              </TouchableOpacity>
            </View>
            {isSchedulePickUpNow ? (
              <View style={orderSummarySty.pickUpOrderDetail}>
                <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                  <CLOCK_IC />
                  <Text allowFontScaling={false} style={orderSummarySty.pickUpOrderDetailText}>
                    {t('YOUR_ORDER_WILL_BE_PLACED_INSTANTLY')}
                  </Text>
                </View>
              </View>
            ) : (
              <InputField
                value={
                  selectedTime?.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true,
                  }) ?? ''
                }
                ref={timeRef}
                onChangeText={() => { }}
                editable={false}
                placeholder={t('SELECT_TIME')}
                leftIcon={<CLOCK_IC />}
                label='Time'
                inputContainerStyle={{ borderColor: _COL.BORDER_FIFTH, backgroundColor: _COL.WHITE }}
                containerStyle={{ marginTop: 18 }}
                viewOnly
                onPressViewOnly={() => setIsTimePickerVisible(true)}
              />
            )}

            {isTimePickerVisible && (
              <RNDateTimePicker
                value={selectedTime || new Date()}
                mode="time"
                is24Hour={true}
                display={isIOS ? 'spinner' : 'clock'}
                onChange={onTimeChange}
                textColor={_COL.FINAL_BLACK}
              />
            )}

            <BTN
              title={t('CONFIRM')}
              onP={handleConfirm}
              borderR={120}
              mTop={18}
            />
          </View>
        </BottomActionSheet>
        <ScheduleCalendarSheet
          isBooking={isSelectedDays}
          data={productDetails?.premeal_info}
          selectedDays={productCheckDetails?.dates}
          setIsBooking={setIsSelectedDays}
          onBook={handleScheduleCheck}
          error={error}
        />
      </View>
      <ConfirmationAlert ref={alertRef} onConfirm={() => { }} t={t} />
    </KeyboardAvoidingView>
  );
};

export default OrderSummary;
