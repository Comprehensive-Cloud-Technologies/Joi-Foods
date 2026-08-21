import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import React, { useContext, useEffect } from 'react';
import { _COL, _HEIGHT, _W, FONT } from 'utils';
import { useT } from 'internationalization';
import { FlatList } from 'react-native-gesture-handler';
import SwipableList from '../components/SwipeList';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { BACK_BTN_IC, EMPTY_IC } from 'assets';
import { ICartProduct, StackProps } from 'types';
import { GETreq } from 'api';
import { AppCtx, storeDataAtom } from 'store';
import { useAtom } from 'jotai';
import { CartItemView, ConfirmationAlert, useLoader } from 'components';
import { SET_CART_DATA } from 'store/context';
import { useAddMinusCart } from 'hooks';

const MyCart = ({ navigation }: StackProps<'MyCart'>) => {
  const { t } = useT();
  const [openedItemId, setOpenedItemId] = React.useState<number | null>(null);
  const insets = useSafeAreaInsets();
  const [storeData] = useAtom(storeDataAtom);
  const { showLoader, hideLoader } = useLoader();
  const { handleIncrement, handleDecrement, deleteCartItem, alertRef } = useAddMinusCart();
  const { dispatch, state: { cartData } } = useContext(AppCtx);

  const getCartData = async () => {
    try {
      showLoader();
      const payload = {
        store_id: storeData.id,
        module: storeData.store_type,
      };
      const { data, success } = await GETreq('cart/list', payload);
      console.log('Get Cart List::', JSON.stringify(data, null, 3));
      if (success) {
        dispatch({ type: SET_CART_DATA, cartData: data?.data });
      }
    } catch (error) {
      console.log(error);
    } finally {
      hideLoader();
    }
  };

  useEffect(() => {
    getCartData();
  }, []);

  const renderMyCartItem = ({
    item,
    index,
  }: {
    item: ICartProduct;
    index: number;
  }) => (
    <>
      <SwipableList
        item={item}
        onDelete={(id: number) => {
          deleteCartItem(id);
        }}
        isSelectionMode={false}
        isSelected={false}
        onToggleSelect={() => { }}
        openedItemId={openedItemId}
        setOpenedItemId={setOpenedItemId}
      >
        <CartItemView
          item={item}
          handleQuantityMinus={() => {
            handleDecrement(item.cart_id?.toString() ?? '');
          }}
          handleQuantityPlus={() => {
            handleIncrement(item.cart_id?.toString() ?? '');
          }}
          vSty={{ padding: 16 }}
        />
      </SwipableList>
      {index !== (cartData?.items ?? []).length - 1 && (
        <View style={styles.myCartDivider} />
      )}
    </>
  );

  return (
    <View
      style={[
        styles.container,
        { paddingTop: insets.top + 12, paddingBottom: insets.bottom },
      ]}
    >
      <View style={styles.row}>
        <TouchableOpacity
          style={styles.backBtn}
          onPress={() => {
            navigation.goBack();
          }}
        >
          <BACK_BTN_IC />
        </TouchableOpacity>

        <Text allowFontScaling={false} style={styles.title}>{t('MY_CART')}</Text>
      </View>
      <View style={styles.divider} />

      <View style={styles.myCartContainer}>
        <FlatList
          data={cartData?.items}
          keyExtractor={item => item?.cart_id?.toString() ?? ''}
          renderItem={renderMyCartItem}
          bounces={false}
          showsVerticalScrollIndicator={false}
          ListEmptyComponent={
            <View style={{ alignItems: 'center', marginTop: _HEIGHT * 0.2 }}>
              <EMPTY_IC />
              <Text allowFontScaling={false}
                style={{
                  textAlign: 'center',
                  marginTop: 8,
                  fontFamily: FONT.REGULAR,
                  fontSize: 14,
                  color: _COL.FINAL_BLACK,
                }}
              >
                {t('YOUR_CART_IS_EMPTY')}
              </Text>
            </View>
          }
        />
      </View>

      {(cartData?.items ?? [])?.length > 0 && (
        <View style={[styles.ordersContainer]}>
          <View style={[styles.row]}>
            <View>
              <Text allowFontScaling={false} style={styles.myCartItemTotal}>
                {t('TOTAL_ITEMS')}
                {cartData?.items?.reduce(
                  (total: number, item: ICartProduct) =>
                    total + (item.quantity ?? 0),
                  0,
                )}
              </Text>
              <Text allowFontScaling={false} style={styles.myCartItemTotalOrderPrice}>
                ₹
                {(cartData?.items ?? [])
                  .reduce(
                    (total: number, item: ICartProduct) =>
                      total + (item.unit_price ?? 0) * (item.quantity ?? 0),
                    0,
                  )
                  .toFixed(2)}
                <Text allowFontScaling={false} style={styles.subtotalText}>{t('SUBTOTAL')}</Text>
              </Text>
            </View>
            <TouchableOpacity
              onPress={() => {
                navigation.navigate('OrderSummary');
              }}
              style={styles.orderNowBtn}
            >
              <Text allowFontScaling={false} style={styles.orderNowText}>{t('ORDER_NOW')}</Text>
            </TouchableOpacity>
          </View>
        </View>
      )}
      <ConfirmationAlert ref={alertRef} onConfirm={() => { }} t={t} />
    </View>
  );
};

export default MyCart;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.WHITE,
    paddingTop: _HEIGHT * 0.01,
  },
  backBtn: {
    position: 'absolute',
    left: 16,
    zIndex: 10,
    bottom: -2,
  },
  row: {
    flexDirection: 'row',
  },
  title: {
    fontFamily: FONT.SEMI_BOLD,
    fontSize: 20,
    textAlign: 'center',
    flex: 1,
    color: _COL.FINAL_BLACK,
    lineHeight: 24,
  },
  divider: {
    height: 1,
    backgroundColor: _COL.BORDER,
    marginTop: 14,
  },
  myCartContainer: {
    flex: 1,
  },
  ordersContainer: {
    marginVertical: 10,
    padding: 16,
    backgroundColor: _COL.WHITE,
    borderRadius: 16,
    marginHorizontal: 16,
    shadowColor: _COL.SHADOW_COLOR,
    shadowOffset: { width: 0, height: 0 },
    shadowOpacity: 0.15,
    shadowRadius: 20,
    elevation: 10,
  },
  myCartItemTotal: {
    fontFamily: FONT.REGULAR,
    fontSize: 12,
    color: _COL.MAIN_BLACK,
    lineHeight: 24,
  },
  myCartItemTotalOrderPrice: {
    fontFamily: FONT.BOLD,
    fontSize: 20,
    color: _COL.FINAL_BLACK,
    marginTop: 4,
    lineHeight: 24,
  },
  subtotalText: {
    fontFamily: FONT.REGULAR,
    fontSize: 12,
    color: _COL.MAIN_BLACK,
    lineHeight: 20,
  },
  orderNowBtn: {
    backgroundColor: _COL.PRIMARY_RED,
    borderRadius: 54,
    paddingVertical: 10,
    paddingHorizontal: 16,
    alignItems: 'center',
    alignSelf: 'center',
    marginLeft: 'auto',
  },
  orderNowText: {
    fontFamily: FONT.SEMI_BOLD,
    fontSize: 14,
    color: _COL.WHITE,
    lineHeight: 24,
  },
  myCartDivider: {
    height: 1,
    backgroundColor: _COL.BORDER,
    marginHorizontal: 16,
    width: _W - 32,
  },
});
