import { useT } from 'internationalization';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { OrderItemT, OrderProductItemT } from 'types';
import { _COL, FONT } from 'utils';
import OrderProductItem from './OrderProductItem';

const OrderItem = ({
  item,
  onPress,
}: {
  item: OrderItemT;
  onPress?: () => void;
}) => {
  const { t } = useT();

  const renderOrderItems = ({ item }: { item: OrderProductItemT }) => {
    return <OrderProductItem item={item} key={item.id?.toString() || ''} />;
  };

  return (
    <Pressable
      style={styles.ordersContainer}
      key={item.id?.toString() || ''}
      onPress={onPress}
    >
      <View style={styles.orderStatusContainer}>
        <View style={styles.rowBetween}>
          <View style={[styles.row, { maxWidth: '55%' }]}>
            <Text allowFontScaling={false}
              style={[styles.itemID, { maxWidth: '55%' }]}
              numberOfLines={1}
              ellipsizeMode="middle"
            >
              {t('ID')} {item.order_number}
            </Text>
            <Text allowFontScaling={false}
              style={[styles.pickup,{maxWidth: '65%'}]}
              numberOfLines={1}
              ellipsizeMode="tail"
            >
              {t('DOT')}{' '}
              {item.is_scheduled ? t('PICKUP_SCHEDULE') : t('PICKUP_INSTANT')}
            </Text>
          </View>
          <View style={[styles.deliveryStatusView,{ backgroundColor: item.status_color + '11'}]}>
            <Text allowFontScaling={false}
              style={[
                styles.deliveryStatus,
                { color: item.status_color,}
              ]}
            >
              {item.status_label}
            </Text>
          </View>
        </View>
        <Text allowFontScaling={false} style={styles.orderDateTime}>{item.formatted_date}</Text>
      </View>

      <View style={{ paddingHorizontal: 16 }}>
        {item.items?.map((item, index) => renderOrderItems({ item }))}
      </View>

      <View style={styles.totalAmountContainer}>
        <Text allowFontScaling={false} style={styles.totalAmountText}>{t('TOTAL_BILL_AMOUNT')}</Text>
        <Text allowFontScaling={false} style={styles.totalAmount}>
          {t('RUPPEE_SYMBOL')}
          {Number(item.total_amount).toFixed(2)}
        </Text>
      </View>
    </Pressable>
  );
};

export default OrderItem;

const styles = StyleSheet.create({
  ordersContainer: {
    borderRadius: 12,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: _COL.BORDER,
  },
  orderStatusContainer: {
    paddingHorizontal: 16,
    paddingTop: 16,
    borderBottomWidth: 1,
    paddingBottom: 12,
    borderStyle: 'dashed',
    borderColor: _COL.BORDER_SIXTH,
  },
  rowBetween: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  orderRowBetween: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 2,
  },
  row: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  itemID: {
    fontSize: 14,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.BLACK,
  },
  pickup: {
    fontSize: 12,
    fontFamily: FONT.MEDIUM,
    color: _COL.TEXT_GREY_LIGHT,
    marginLeft: 8,
  },
  deliveryStatusView: {
    paddingHorizontal: 12,
    paddingVertical: 4,
    borderRadius: 42,
  },
  deliveryStatus: {
    fontSize: 12,
    fontFamily: FONT.SEMI_BOLD,
    borderRadius: 42,
  },
  orderDateTime: {
    fontSize: 12,
    fontFamily: FONT.REGULAR,
    color: _COL.BLACK,
    marginTop: 2,
  },
  orderItemsContainer: {
    paddingHorizontal: 16,
    marginTop: 16,
  },
  orderItemImage: {
    width: 54,
    height: 54,
    borderRadius: 8,
  },
  orderItemDetails: {
    flex: 1,
    marginLeft: 12,
  },
  orderItemName: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    color: _COL.FINAL_BLACK,
  },
  orderItemQuantity: {
    fontSize: 12,
    fontFamily: FONT.REGULAR,
    color: _COL.TEXT_GREY_LIGHT,
  },
  orderItemPrice: {
    fontSize: 14,
    fontFamily: FONT.MEDIUM,
    color: _COL.FINAL_BLACK,
  },
  totalAmountContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    borderTopWidth: 1,
    marginTop: 16,
    borderColor: _COL.BORDER_SIXTH,
    borderStyle: 'dashed',
  },
  totalAmountText: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    color: _COL.FINAL_BLACK,
  },
  totalAmount: {
    fontSize: 14,
    fontFamily: FONT.BOLD,
    color: _COL.FINAL_BLACK,
  },
  vegNonVegIcon: {
    resizeMode: 'contain',
    position: 'absolute',
    top: 5,
    right: 5,
  },
});
