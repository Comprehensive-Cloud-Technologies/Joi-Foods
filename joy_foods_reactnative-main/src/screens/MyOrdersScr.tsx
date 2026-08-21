import { StyleSheet, Text, View, Image, RefreshControl, ActivityIndicator, FlatList } from 'react-native';
import React, { useContext, useEffect, useState } from 'react';
import { _COL, _H, FONT } from 'utils';
import { useT } from 'internationalization';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { EMPTY_ORDERS_ICON } from 'assets';
import { GETreq } from 'api';
import { useAtom } from 'jotai';
import { AppCtx, storeDataAtom } from 'store';
import { SET_ORDER_DATA } from 'store/context';
import { NavProps, OrderItemT } from 'types';
import { OrderItem, useLoader } from 'components';

const MyOrdersScr = ({ navigation }: NavProps<'Orders'>) => {
  const { t } = useT();
  const { top } = useSafeAreaInsets();
  const {
    dispatch,
    state: { orderData },
  } = useContext(AppCtx);

  const [storeData] = useAtom(storeDataAtom);

  const [isRefreshing, setIsRefreshing] = useState(false);
  const [page, setPage] = useState(1);
  const [bottomLoading, setBottomLoading] = useState(false);
  const [hasNext, setHasNext] = useState(true);
  const { showLoader, hideLoader } = useLoader();

  const getOrderData = async (isRefresh = false) => {
    try {
      if (!orderData) showLoader();
      if (!isRefresh && !hasNext) return;
      if (bottomLoading) return;
      if (isRefresh) {
        setPage(1);
        setIsRefreshing(true);
      } else {
        setBottomLoading(true);
      }
      const payload = {
        module: storeData.store_type,
        page: isRefresh ? 1 : page,
        per_page: 10,
      };
      const { data, success } = await GETreq('my_orders/list', payload);
      console.log('Order DATA::', JSON.stringify(data, null, 3));
      if (success) {
        if (isRefresh) {
          dispatch({ type: SET_ORDER_DATA, orderData: data?.data?.orders });
        } else {
          dispatch({
            type: SET_ORDER_DATA,
            orderData: [...(orderData || []), ...data?.data?.orders],
          });
        }
        const pagination = data.data.pagination;
        setHasNext(pagination?.has_next || false);
        setPage(pagination?.current_page + 1);
        setIsRefreshing(false);
        setBottomLoading(false);
        hideLoader();
      }
    } catch (e) {
      console.log(e);
      setIsRefreshing(false);
      setBottomLoading(false);
      hideLoader();
    }
  };

  useEffect(() => {
    getOrderData();
  }, []);

  const handleEndReached = () => {
    if (hasNext) {
      getOrderData();
    }
  };

  const renderMyOrderItem = ({ item }: { item: OrderItemT }) => {
    return (
      <OrderItem
        item={item}
        key={item.id?.toString() || ''}
        onPress={() => {
          navigation.navigate('OrderDetailsScr', {
            orderId: item.id?.toString() || '',
          });
        }}
      />
    );
  };

  return (
    <View style={styles.container}>
      <Text allowFontScaling={false} style={[styles.heading, { marginTop: top + 13 }]}>
        {t('MY_ORDERS')}
      </Text>


      <FlatList
        data={orderData}
        keyExtractor={(item, index) =>
          item.id?.toString() || index.toString()
        }
        renderItem={renderMyOrderItem}
        contentContainerStyle={styles.ordersContentContainer}
        onEndReached={handleEndReached}
        onEndReachedThreshold={0.5}
        refreshControl={
          <RefreshControl
            refreshing={isRefreshing}
            onRefresh={() => getOrderData(true)}
          />
        }
        ListFooterComponent={
          bottomLoading ? (
            <ActivityIndicator size="large" color={_COL.MAIN_BLACK} />
          ) : null
        }
        showsVerticalScrollIndicator={false}
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Image source={EMPTY_ORDERS_ICON} />
            <Text allowFontScaling={false} style={styles.emptyText}>{t('ORDER_LIST_EMPTY')}</Text>
          </View>
        }
      />

    </View>
  );
};

export default MyOrdersScr;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.WHITE,
  },
  heading: {
    fontSize: 24,
    fontFamily: FONT.BOLD,
    color: _COL.FINAL_BLACK,
    textAlign: 'left',
    marginLeft: 16,
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: _H / 7
  },
  emptyText: {
    fontSize: 14,
    marginHorizontal: 80,
    fontFamily: FONT.REGULAR,
    color: _COL.MAIN_BLACK,
    textAlign: 'center',
    marginTop: 7,
  },
  ordersContentContainer: {
    paddingHorizontal: 16,
    marginTop: 18,
    paddingBottom: 30,
  },
});
