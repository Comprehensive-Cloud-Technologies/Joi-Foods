import { View, Text, StyleSheet, ScrollView } from 'react-native';
import React, { useState } from 'react';
import { _COL, _W, FONT } from 'utils';
import { SUCCESSFULL_IC } from 'assets';
import { useT } from 'internationalization';
import { BTN, FeedbackSheet, PickUpQR, QRCodeView } from 'components';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { StackProps } from 'types';
import StoreType from 'types/StoreTypes';
import { storeDataAtom } from 'store/atoms';
import { useAtom } from 'jotai';
import TextButton from 'components/TextButton';

const OrderSuccessfullScr = ({ navigation, route: { params: { orderData, storeType } } }: StackProps<'OrderSuccessfull'>) => {
  const { t } = useT();
  const { top } = useSafeAreaInsets();
  const [isFeedBack, setIsFeedBack] = useState(false);
  const [isPickUpQR, setIsPickUpQR] = useState(false);
  const [storeData] = useAtom(storeDataAtom);
  const store_type = storeType || storeData?.store_type;
  console.log('OrderData::', JSON.stringify(orderData, null, 3));

  return (
    <View style={styles.container}>
      <ScrollView
        showsVerticalScrollIndicator={false}>
        <SUCCESSFULL_IC style={[styles.successIcon, { marginTop: top + 128 }]} />
        <Text allowFontScaling={false} style={styles.heading}>{t('ORDER_PLACED')}</Text>
        <Text allowFontScaling={false} style={styles.subHeading}>{t('ORDER_SUBMITTED_SUCCESSFULLY')}</Text>

        <View style={styles.orderCodesContainer}>
          <View style={styles.orderCodeContainer}>
            <Text allowFontScaling={false} style={styles.orderCodeLabel}>{t('ORDER_ID')}</Text>
            <Text allowFontScaling={false} style={styles.orderID} numberOfLines={2} adjustsFontSizeToFit>
              {(StoreType.QSR === store_type || StoreType.KOT === store_type)
                ? orderData?.order?.order_number
                : orderData?.data?.orders?.[0]?.order_number}
            </Text>
          </View>
          <View style={styles.codesDivider} />
          <View style={styles.orderCodeContainer}>
            <Text allowFontScaling={false} style={styles.orderCodeLabel}>{t('ORDER_CODE')}</Text>
            <Text allowFontScaling={false} style={styles.orderCode} numberOfLines={1} ellipsizeMode='middle'>
              {(StoreType.QSR === store_type || StoreType.KOT === store_type)
                ? orderData?.order?.pickup_code
                : orderData?.data?.orders?.[0]?.pickup_code}
            </Text>
          </View>
        </View>

        <PickUpQR
          pickUpCode={
            (StoreType.QSR === store_type || StoreType.KOT === store_type)
              ? orderData?.order?.pickup_code ?? ''
              : orderData?.data?.orders?.[0]?.pickup_code ?? ''
          }
          qrData={
            (StoreType.QSR === store_type || StoreType.KOT === store_type)
              ? orderData?.order?.qr_data ?? ''
              : orderData?.data?.orders?.[0]?.qr_data ?? ''
          }
          onPress={() => setIsPickUpQR(true)}
        />

        <View style={styles.btnContainer}>
          <BTN
            title={t('VIEW_ORDER_DETAILS')}
            onP={() =>
              navigation.navigate('OrderDetailsScr', {
                orderId:
                  StoreType.PREMEAL === store_type
                    ? orderData?.data?.orders?.[0]?.order_id?.toString() || ''
                    : orderData?.order?.id?.toString() || ''
              })
            }
            borderR={120}
            mTop={30}
            bgCol={_COL.WHITE}
            tCol={_COL.FINAL_BLACK}
            borderW={1}
            borderCol={_COL.MAIN_BLACK}
            bordered={true}
          />
          {/* <BTN
            title={t('GIVE_YOUR_FEEDBACK')}
            onP={() => setIsFeedBack(true)}
            borderR={120}
            mTop={13}
            bgCol={_COL.WHITE}
            tCol={_COL.FINAL_BLACK}
            borderW={1}
            borderCol={_COL.MAIN_BLACK}
            bordered={true}
          /> */}

          {/* <TouchableOpacity
            onPress={() => {
              navigation.pop(2);
            }}
          >
            <Text allowFontScaling={false} style={styles.closeBtnText}>{t('CLOSE')}</Text>
          </TouchableOpacity> */}
          <TextButton
            onP={() => {
              navigation.pop(2);
            }}
            isUnderline={true}
            style={{ marginTop: 43 }}
            text={t('CLOSE')}
          />
        </View>
      </ScrollView>

      <FeedbackSheet
        isVisible={isFeedBack}
        setIsVisible={() => setIsFeedBack(false)}
      />

      {isPickUpQR && (
        <QRCodeView
          isPickUpQR={isPickUpQR}
          setIsPickUpQR={setIsPickUpQR}
          qrData={
            (StoreType.QSR === store_type || StoreType.KOT === store_type)
              ? orderData?.order?.qr_data ?? ''
              : StoreType.PREMEAL === store_type
                ? orderData?.data?.orders?.[0]?.qr_data ?? ''
                : ''
          }
          pickupCode={
            (StoreType.QSR === store_type || StoreType.KOT === store_type)
              ? orderData?.order?.pickup_code ?? ''
              : StoreType.PREMEAL === store_type
                ? orderData?.data?.orders?.[0]?.pickup_code ?? ''
                : ''
          }
        />
      )}
    </View>
  );
};

export default OrderSuccessfullScr;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.WHITE,
  },
  successIcon: {
    alignSelf: 'center',
  },
  heading: {
    fontSize: 24,
    fontFamily: FONT.BOLD,
    marginTop: 19,
    color: _COL.FINAL_BLACK,
    textAlign: 'center',
  },
  subHeading: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    marginTop: 7,
    color: _COL.TEXT_GREY,
    textAlign: 'center',
  },
  orderCodesContainer: {
    flexDirection: 'row',
    marginTop: 32,
    paddingHorizontal: 16,
    justifyContent: "space-around"
  },
  orderCodeContainer: {
    paddingHorizontal: 8
  },
  orderCodeLabel: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    color: _COL.TEXT_GREY_LIGHT,
    textAlign: 'center',
  },
  orderCode: {
    fontSize: 16,
    fontFamily: FONT.MEDIUM,
    color: _COL.FINAL_BLACK,
    marginTop: 4,
    textAlign: 'center',
  },
  orderID: {
    fontSize: 16,
    fontFamily: FONT.MEDIUM,
    color: _COL.FINAL_BLACK,
    marginTop: 4,
    textAlign: 'center',
    width: _W * 0.22
  },
  codesDivider: {
    height: '100%',
    borderWidth: 1,
    borderColor: _COL.BORDER,
  },
  btnContainer: {
    marginHorizontal: 24,
  },
});
