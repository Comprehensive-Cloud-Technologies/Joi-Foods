import React from 'react';
import { FlatList, ScrollView, Text, TouchableOpacity, View } from 'react-native';
import { _COL, _W, _WIDTH } from 'utils';
import { BACK_BTN_IC, NON_VEG_IC, VEG_IC } from 'assets';
import { ScheduledOrderT, StackProps } from 'types';
import { BTN, CancelOrderSheet, FeedbackSheet, OrderProductItem, OrderStatus, PickUpQR, QRCodeView } from 'components';
import PreMealCancelOrderSheet from 'components/MyOrders/PreMealCancelOrderSheet';
import StoreType from 'types/StoreTypes';
import { useOrderDetails } from 'hooks';
import { orderDetailsSty } from 'styles';

const OrderDetailsScr = (Props: StackProps<'OrderDetailsScr'>) => {

  const {
    bottom, cancelOrder, cancelOrderDays, handelCancelOrder, isCancelOrderSheetVisible,
    navigation, orderDetails, setIsPickUpQR, t, top, setIsCancelOrderSheetVisible,
    isCancel, isPickUpQR, handleReorder, setIsFeedBack, isFeedBack, handleFeedbackSuccess
  } = useOrderDetails(Props);

  const renderMealSchedule = ({ item, index }: { item: ScheduledOrderT; index: number }) => {
    return (
      <View style={orderDetailsSty.mealScheduleItemContainer} key={index}>
        <View style={orderDetailsSty.billInfoRow}>
          <View style={[orderDetailsSty.leftRow, { alignItems: 'center' }]}>
            <Text allowFontScaling={false} style={orderDetailsSty.mealScheduleItemDay}>
              {item?.formatted_date}
              {`, ${new Date(item?.scheduled_date ?? '').toLocaleDateString('en-US', { weekday: 'long' })}`}
            </Text>
            <View style={[orderDetailsSty.mealScheduleStatus,
            { backgroundColor: item?.status_color + '20' }]}>
              <Text allowFontScaling={false} style={[orderDetailsSty.mealScheduleStatusText,
              { color: item?.status_color }]}>
                {item?.status_label}
              </Text>
            </View>
          </View>

          <Text allowFontScaling={false} style={orderDetailsSty.mealScheduleItemPrice}>₹ {item?.total_amount?.toFixed(2)}</Text>
        </View>
        <View style={orderDetailsSty.rowBetween}>
          <Text allowFontScaling={false} style={orderDetailsSty.mealScheduleItems}>
            {item?.items_string}
          </Text>
          {item?.is_vegetarian ? (<VEG_IC style={orderDetailsSty.vegNonVegIcon} />) : (<NON_VEG_IC style={orderDetailsSty.vegNonVegIcon} />)}
        </View>
      </View>
    );
  }

  const renderPremealDays = ({ item, index }: { item: ScheduledOrderT; index: number }) => {
    return (
      <View
        key={index}
        style={[orderDetailsSty.preMealScheduleDaysContainer, { marginHorizontal: index === 0 ? 0 : 4 }]}
      >
        <Text allowFontScaling={false} style={orderDetailsSty.preMealScheduleDays}>
          {new Date(item?.scheduled_date ?? '').toLocaleDateString('en-US', { weekday: 'long' }).slice(0, 3)}
        </Text>
      </View>
    );
  }

  return (
    <View style={[orderDetailsSty.container, { paddingTop: top + 12 }]}>
      <View style={orderDetailsSty.row}>
        <TouchableOpacity
          style={orderDetailsSty.backBtn}
          onPress={() => navigation.goBack()}
        >
          <BACK_BTN_IC />
        </TouchableOpacity>
        <Text allowFontScaling={false} style={orderDetailsSty.title}>{t('ORDER_DETAILS')}</Text>
      </View>

      <ScrollView
        showsVerticalScrollIndicator={false}
      >
        {(StoreType.QSR === orderDetails?.module || StoreType.KOT === orderDetails?.module) &&
          <OrderStatus
            status={orderDetails?.status ?? ''}
            statuses={orderDetails?.statuses ?? []}
          />}

        <View style={{ paddingHorizontal: 16 }}>
          {(StoreType.QSR === orderDetails?.module || StoreType.KOT === orderDetails?.module) && (
            <View style={[orderDetailsSty.secondRow, { maxWidth: '100%' }]}>
              <Text allowFontScaling={false}
                style={[orderDetailsSty.itemID, { maxWidth: '50%' }]}
                numberOfLines={1}
                ellipsizeMode="middle"
              >
                {t('ID')} {orderDetails?.order_number}
              </Text>
              <Text allowFontScaling={false}
                style={[orderDetailsSty.pickup, { maxWidth: '48%', lineHeight: 24 }]}
                numberOfLines={1}
                ellipsizeMode="middle"
              >
                {t('DOT')}
                {orderDetails?.formatted_date}
              </Text>
            </View>
          )}

          {orderDetails?.pickup?.qr_data &&
            (StoreType.QSR === orderDetails?.module || StoreType.KOT === orderDetails?.module) && orderDetails?.status !== 'CANCELLED' ? (
            <PickUpQR
              pickUpCode={orderDetails?.pickup?.code ?? ''}
              qrData={orderDetails?.pickup?.qr_data}
              onPress={() => setIsPickUpQR(true)}
              vSty={{ marginHorizontal: 0, marginTop: 15 }}
            />
          ) : StoreType.PREMEAL === orderDetails?.module && isCancel && (
            <PickUpQR
              pickUpCode={orderDetails?.pickup?.code ?? ''}
              qrData={orderDetails?.pickup?.qr_data ?? ''}
              onPress={() => setIsPickUpQR(true)}
              vSty={{ marginHorizontal: 0, marginTop: 15 }}
            />
          )}

          {StoreType.PREMEAL === orderDetails?.module && (
            <View
              style={{
                marginTop: 20,
              }}
            >
              <View style={orderDetailsSty.secondRowBetween}>
                <Text allowFontScaling={false} style={orderDetailsSty.preMealScheduleTitle}>{t('PRE_MEAL_SCHEDULE')}</Text>
                <View style={orderDetailsSty.preMealScheduleView}>
                  {orderDetails?.is_scheduled && (
                    <Text allowFontScaling={false} style={orderDetailsSty.preMealScheduleText}>
                      {t('SCHEDULED')}
                    </Text>
                  )}
                </View>
              </View>
              <ScrollView
                horizontal
                showsHorizontalScrollIndicator={false}
                contentContainerStyle={{ gap: 5 }}
                style={{ marginTop: 5, marginBottom: 6 }}
              >
                {/* <View
                  style={orderDetailsSty.preMealScheduleDaysContainer}
                >
                  <Text allowFontScaling={false}
                    style={orderDetailsSty.preMealScheduleDays}
                  >
                    {new Date(orderDetails?.scheduled_date ?? '').toLocaleDateString('en-US', { weekday: 'long' }).slice(0, 3)}
                  </Text>
                </View> */}

                <FlatList
                  data={orderDetails?.scheduled_orders}
                  horizontal
                  scrollEnabled={false}
                  showsHorizontalScrollIndicator={false}
                  renderItem={renderPremealDays}
                  keyExtractor={(item, index) => `${item?.id}-${index}`}
                  contentContainerStyle={{ paddingRight: 6 }}
                />

              </ScrollView>
              <View style={orderDetailsSty.leftRow}>
                <Text allowFontScaling={false} style={orderDetailsSty.timingText}>{t('TIMING')}</Text>
                <Text allowFontScaling={false} style={orderDetailsSty.timing}>{orderDetails?.formatted_pickup_date_time?.slice(10)}</Text>
              </View>

              <View style={orderDetailsSty.divider} />

              <Text allowFontScaling={false} style={orderDetailsSty.mealScheduleText}>{t('MEAL_SCHEDULE')}</Text>

              {/* <View style={orderDetailsSty.mealScheduleItemContainer}>
                <View style={orderDetailsSty.billInfoRow}>
                  <View style={orderDetailsSty.leftRow}>
                    <Text allowFontScaling={false} style={orderDetailsSty.mealScheduleItemDay}>
                      {orderDetails?.formatted_scheduled_date}
                      {`, ${new Date(orderDetails?.scheduled_date ?? '').toLocaleDateString('en-US', { weekday: 'long' })}`}
                    </Text>
                    <View style={[orderDetailsSty.mealScheduleStatus,
                    { backgroundColor: orderDetails?.status_color + '20' }]}>
                      <Text allowFontScaling={false} style={[orderDetailsSty.mealScheduleStatusText,
                      { color: orderDetails?.status_color }]}>
                        {orderDetails?.status_label}
                      </Text>
                    </View>
                  </View>

                  <Text allowFontScaling={false} style={orderDetailsSty.mealScheduleItemPrice}>₹ {orderDetails?.items?.[0]?.total_price?.toFixed(2)}</Text>
                </View>
                <View style={orderDetailsSty.rowBetween}>
                  <Text allowFontScaling={false} style={orderDetailsSty.mealScheduleItems}>
                    
                    Chapati Roti,Jeera Rice,Dal Tadka,Paneer Butter Masala,Palak Paneer,Papad,Butter Milk
                  </Text>
                  {orderDetails?.items?.[0]?.is_vegetarian ? (<VEG_IC style={orderDetailsSty.vegNonVegIcon} />) : (<NON_VEG_IC style={orderDetailsSty.vegNonVegIcon} />)}
                </View>
              </View> */}

              <FlatList
                data={orderDetails?.scheduled_orders}
                scrollEnabled={false}
                showsVerticalScrollIndicator={false}
                renderItem={renderMealSchedule}
                keyExtractor={(item, index) => `${item?.id}-${index}`}
                contentContainerStyle={{ gap: 14, marginTop: 14 }}
              />
            </View>
          )}
          {(StoreType.QSR === orderDetails?.module || (StoreType.KOT === orderDetails?.module && orderDetails?.is_scheduled)) && (
            <View style={orderDetailsSty.pickupScheduleContainer}>
              <View style={orderDetailsSty.rowBetween}>
                <Text allowFontScaling={false} style={orderDetailsSty.scheduleTitle}>
                  {orderDetails?.is_scheduled
                    ? t('PICKUP_SCHEDULE')
                    : t('PICKUP_INSTANT')}
                </Text>
                <Text allowFontScaling={false}
                  style={[
                    orderDetailsSty.deliveryStatus,
                    {
                      color: orderDetails?.status_color,
                      backgroundColor: orderDetails?.status_color + '11',
                    },
                  ]}
                >
                  {orderDetails?.status_label}
                </Text>
              </View>
              {orderDetails?.formatted_pickup_date_time && (
                <Text allowFontScaling={false} style={orderDetailsSty.scheduleTimeText}>
                  {t('TIME')}{' '}
                  <Text allowFontScaling={false} style={orderDetailsSty.scheduleTime}>
                    {orderDetails?.formatted_pickup_date_time}
                  </Text>
                </Text>
              )}
            </View>
          )}

          {(StoreType.QSR === orderDetails?.module || StoreType.KOT === orderDetails?.module) && (
            <View
              style={{
                paddingTop: 16,
                borderBottomWidth: 1,
                paddingBottom: 20,
                borderBottomColor: _COL.BORDER_NINTH,
              }}
            >
              <Text allowFontScaling={false} style={orderDetailsSty.pageTitles}>{t('ITEMS')}</Text>
              {orderDetails?.items?.map(order => (
                <OrderProductItem key={order.id} item={order} />
              ))}
            </View>
          )}

          {(StoreType.QSR === orderDetails?.module || StoreType.KOT === orderDetails?.module) && (
            <View
              style={{
                borderBottomWidth: 1,
                paddingBottom: 18,
                borderBottomColor: _COL.BORDER_NINTH,
                paddingTop: 16,
              }}
            >
              <Text allowFontScaling={false} style={orderDetailsSty.pageTitles}>{t('OUTLET_ADDRESS')}</Text>
              <Text allowFontScaling={false} style={orderDetailsSty.outletAddressText}>
                {orderDetails?.store?.address?.length !== 0
                  ? orderDetails?.store?.address
                  : '---'}
              </Text>
            </View>
          )}
          {StoreType.KOT === orderDetails?.module && (
            <View
              style={{
                borderBottomWidth: 1,
                paddingBottom: 18,
                borderBottomColor: _COL.BORDER_NINTH,
                paddingTop: 16,
              }}
            >
              <Text allowFontScaling={false} style={orderDetailsSty.pageTitles}>Delivery Location</Text>
              <Text allowFontScaling={false} style={orderDetailsSty.outletAddressText}>
                {orderDetails?.delivery_location?.name?.length !== 0
                  ? orderDetails?.delivery_location?.name
                  : '---'}
              </Text>
            </View>
          )}
          {StoreType.KOT === orderDetails?.module && (
            <View
              style={{
                borderBottomWidth: 1,
                paddingBottom: 18,
                borderBottomColor: _COL.BORDER_NINTH,
                paddingTop: 16,
              }}
            >
              <Text allowFontScaling={false} style={orderDetailsSty.pageTitles}>Department</Text>
              <Text allowFontScaling={false} style={orderDetailsSty.outletAddressText}>
                {orderDetails?.department?.name?.length !== 0
                  ? orderDetails?.department?.name
                  : '---'}
              </Text>
            </View>
          )}

          {/* {orderDetails?.formatted_pickup_date_time && (

            {orderDetails?.formatted_pickup_date_time && (

              <Text allowFontScaling={false} style={orderDetailsSty.scheduleTimeText}>
                {t('TIME')}{' : '}
                <Text allowFontScaling={false} style={orderDetailsSty.scheduleTime}>
                  {orderDetails?.formatted_pickup_date_time}
                </Text>
              </Text>
            )} */}
          {/* </View> */}

          <View style={{ marginBottom: bottom + 20, marginTop: 16 }}>
            <Text allowFontScaling={false} style={orderDetailsSty.pageTitles}>{t('BILL_INFO')}</Text>
            <View style={orderDetailsSty.firstRowBetween}>
              <Text allowFontScaling={false} style={orderDetailsSty.billInfoSubtitle}>{t('PRICE')}</Text>
              <Text allowFontScaling={false} style={orderDetailsSty.billInfoPrice}>
                {(StoreType.QSR === orderDetails?.module || StoreType.KOT === orderDetails?.module)
                  ? orderDetails?.pricing?.formatted_subtotal
                  : orderDetails?.booking_summary?.formatted_items_total}
              </Text>
            </View>
            {(StoreType.QSR === orderDetails?.module || StoreType.KOT === orderDetails?.module) && orderDetails?.pricing?.formatted_tax !== '0' && (
              <View style={[orderDetailsSty.secondRowBetween, { marginTop: 8 }]}>
                <Text allowFontScaling={false} style={orderDetailsSty.billInfoSubtitle}>{t('GST')}</Text>
                <Text allowFontScaling={false} style={orderDetailsSty.billInfoPrice}>
                  {orderDetails?.pricing?.formatted_tax}
                </Text>
              </View>
            )}
            {StoreType.PREMEAL === orderDetails?.module && orderDetails?.booking_summary?.formatted_tax !== undefined && (
              <View style={[orderDetailsSty.secondRowBetween, { marginTop: 8 }]}>
                <Text allowFontScaling={false} style={orderDetailsSty.billInfoSubtitle}>{t('GST')}</Text>
                <Text allowFontScaling={false} style={orderDetailsSty.billInfoPrice}>
                  {orderDetails?.booking_summary?.formatted_tax}
                </Text>
              </View>
            )}
            {(StoreType.QSR === orderDetails?.module || StoreType.KOT === orderDetails?.module) && orderDetails?.pricing?.company_contribution !== 0 && (
              <View style={[orderDetailsSty.secondRowBetween, { marginTop: 8 }]}>
                <Text allowFontScaling={false} style={orderDetailsSty.billInfoSubtitle}>{t('COMPANY_CONTRIBUTION')}</Text>
                <Text allowFontScaling={false} style={orderDetailsSty.billInfoPrice}>
                  {orderDetails?.pricing?.formatted_company_contribution}
                </Text>
              </View>
            )}
            {StoreType.PREMEAL === orderDetails?.module && orderDetails?.booking_summary?.company_contribution !== 0 && (
              <View style={[orderDetailsSty.secondRowBetween, { marginTop: 8 }]}>
                <Text allowFontScaling={false} style={orderDetailsSty.billInfoSubtitle}>{t('COMPANY_CONTRIBUTION')}</Text>
                <Text allowFontScaling={false} style={orderDetailsSty.billInfoPrice}>
                  {orderDetails?.booking_summary?.formatted_company_contribution}
                </Text>
              </View>
            )}

            <View style={orderDetailsSty.secondDivider} />

            <View style={{ paddingVertical: 12 }}>
              <View
                style={[
                  orderDetailsSty.firstRowBetween,
                  {
                    // marginTop:
                    //   orderDetails?.payment?.method == 'MIXED' ||
                    //     orderDetails?.pricing?.discount !== 0
                    //     ? 10
                    //     : 0,
                    marginBottom: 12,
                  },
                ]}
              >
                <Text allowFontScaling={false} style={orderDetailsSty.billInfoPrice}>
                  {t('AMOUNT_TO_BE_PAID')}
                </Text>
                <Text allowFontScaling={false} style={orderDetailsSty.billInfoPrice}>
                  {(StoreType.QSR === orderDetails?.module) ? (
                    orderDetails?.pricing?.formatted_total
                  ) : StoreType.KOT === orderDetails?.module ? (
                    orderDetails?.pricing?.formatted_employee_contribution
                  ) : (
                    orderDetails?.booking_summary?.formatted_employee_contribution
                  )}
                </Text>
              </View>
              {(StoreType.QSR === orderDetails?.module || StoreType.KOT === orderDetails?.module) && (
                orderDetails?.pricing?.discount !== 0 && (
                  <View style={orderDetailsSty.firstRowBetween}>
                    <Text allowFontScaling={false} style={orderDetailsSty.billInfoSubtitle}>
                      {t('COUPON_APPLIED')}
                    </Text>
                    <Text allowFontScaling={false} style={orderDetailsSty.couponText}>
                      -{orderDetails?.pricing?.formatted_discount}
                    </Text>
                  </View>
                )
              )}
              {StoreType.PREMEAL === orderDetails?.module && (
                orderDetails?.booking_summary?.discount !== 0 && (
                  <View style={orderDetailsSty.firstRowBetween}>
                    <Text allowFontScaling={false} style={orderDetailsSty.billInfoSubtitle}>
                      {t('COUPON_APPLIED')}
                    </Text>
                    <Text allowFontScaling={false} style={orderDetailsSty.couponText}>
                      -{orderDetails?.booking_summary?.formatted_discount}
                    </Text>
                  </View>
                )
              )}
              {(StoreType.QSR === orderDetails?.module || StoreType.KOT === orderDetails?.module) && (
                (orderDetails?.pricing?.wallet_deducted ?? 0) > 0 && (
                  <View style={[orderDetailsSty.secondRowBetween]}>
                    <Text allowFontScaling={false} style={orderDetailsSty.billInfoSubtitle}>{t('WALLET_PAY')}</Text>
                    <Text allowFontScaling={false} style={orderDetailsSty.walletText}>
                      -{orderDetails?.pricing?.formatted_wallet_deducted}
                    </Text>
                  </View>
                )
              )}
              {StoreType.PREMEAL === orderDetails?.module && (
                (orderDetails?.booking_summary?.wallet_deducted ?? 0) > 0 && (
                  <View style={[orderDetailsSty.secondRowBetween]}>
                    <Text allowFontScaling={false} style={orderDetailsSty.billInfoSubtitle}>{t('WALLET_PAY')}</Text>
                    <Text allowFontScaling={false} style={orderDetailsSty.walletText}>
                      -{orderDetails?.booking_summary?.formatted_wallet_deducted}
                    </Text>
                  </View>
                )
              )}
              {(orderDetails?.payment?.method == 'MIXED' ||
                orderDetails?.pricing?.discount !== 0) && (
                  <View style={orderDetailsSty.secondDivider} />
                )}
              {/* <View
                style={[
                  orderDetailsSty.firstRowBetween,
                  {
                    marginTop:
                      orderDetails?.payment?.method == 'MIXED' ||
                        orderDetails?.pricing?.discount !== 0
                        ? 10
                        : 0,
                  },
                ]}
              >
                <Text allowFontScaling={false} style={orderDetailsSty.billInfoPrice}>
                  {t('AMOUNT_TO_BE_PAID')}
                </Text>
                <Text allowFontScaling={false} style={orderDetailsSty.billInfoPrice}>
                  {(StoreType.QSR === orderDetails?.module) ? (
                    orderDetails?.pricing?.formatted_total
                  ) : StoreType.KOT === orderDetails?.module ? (
                    orderDetails?.pricing?.formatted_online_paid
                  ) : (
                    orderDetails?.booking_summary?.formatted_online_paid
                  )}
                </Text>
              </View> */}
              <View style={orderDetailsSty.secondDivider} />
            </View>

            <View style={[orderDetailsSty.firstRowBetween, { marginTop: 4 }]}>
              <Text allowFontScaling={false} style={orderDetailsSty.billInfoPrice}>{t('TOTAL_AMOUNT')}</Text>
              <Text allowFontScaling={false} style={orderDetailsSty.totalAmountPrice}>
                {(StoreType.QSR === orderDetails?.module || StoreType.KOT === orderDetails?.module) ? (
                  orderDetails?.pricing?.formatted_online_paid
                ) : (
                  orderDetails?.booking_summary?.formatted_online_paid
                )}
              </Text>
            </View>

            {StoreType.PREMEAL == orderDetails?.module && !orderDetails?.cancellation?.can_cancel ?
              null :
              <BTN
                title={orderDetails?.cancellation?.can_cancel ? t('CANCEL_ORDER') : t('REORDER')}
                onP={orderDetails?.cancellation?.can_cancel ? handelCancelOrder : handleReorder}
                borderR={64}
                mTop={24}
                // isDisabled={(StoreType.QSR === orderDetails?.module || StoreType.KOT === orderDetails?.module) && false}
                // btnSty={(StoreType.QSR === orderDetails?.module || StoreType.KOT === orderDetails?.module) && { opacity: 1 }}
                bgCol={orderDetails?.cancellation?.can_cancel ? _COL.WHITE : _COL.PRIMARY_RED}
                tCol={orderDetails?.cancellation?.can_cancel ? _COL.FINAL_BLACK : _COL.WHITE}
                borderW={1}
                borderCol={_COL.MAIN_BLACK}
                bordered={orderDetails?.cancellation?.can_cancel ? true : false}
              />
            }

            {orderDetails?.collect_review && (
              <BTN
                title={t('GIVE_YOUR_FEEDBACK')}
                onP={() => setIsFeedBack(true)}
                borderR={120}
                mTop={13}
                bgCol={_COL.WHITE}
                tCol={_COL.FINAL_BLACK}
                borderW={1}
                borderCol={_COL.MAIN_BLACK}
                bordered={true}
              />
            )}
          </View>
        </View>
      </ScrollView>
      {isPickUpQR && (
        <QRCodeView
          isPickUpQR={isPickUpQR}
          setIsPickUpQR={setIsPickUpQR}
          qrData={orderDetails?.pickup?.qr_data ?? ''}
          pickupCode={orderDetails?.pickup?.code ?? ''}
        />
      )}
      {(StoreType.QSR === orderDetails?.module || StoreType.KOT === orderDetails?.module) ? (
        <CancelOrderSheet
          isVisible={isCancelOrderSheetVisible}
          setIsVisible={() => setIsCancelOrderSheetVisible(false)}
          onProceed={cancelOrder}
        />
      ) : (
        <PreMealCancelOrderSheet
          isVisible={isCancelOrderSheetVisible}
          setIsVisible={() => setIsCancelOrderSheetVisible(false)}
          onProceed={cancelOrder}
          selectDatesData={cancelOrderDays}
        />
      )}

      <FeedbackSheet
        isVisible={isFeedBack}
        setIsVisible={() => setIsFeedBack(false)}
        orderId={orderDetails?.id}
        onSuccess={handleFeedbackSuccess}
      />
    </View>
  );
};

export default OrderDetailsScr;