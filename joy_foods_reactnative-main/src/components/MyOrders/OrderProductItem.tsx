import { NON_VEG_IC, VEG_IC } from "assets";
import { useT } from "internationalization";
import { Image, StyleSheet, Text, View } from "react-native";
import { OrderProductItemT } from "types";
import { _COL, FONT } from "utils";

const OrderProductItem = ({item}: {item: OrderProductItemT}) => {
    const {t}=useT();
    return (
       <View style={[styles.row, styles.orderItemsContainer]} key={item.id}>
        <View>
          <Image
            source={{ uri: item.thumbnail }}
            style={styles.orderItemImage}
          />
          {item.is_vegetarian ? (
            <VEG_IC height={13} width={13} style={styles.vegNonVegIcon} />
          ) : (
            <NON_VEG_IC height={13} width={13} style={styles.vegNonVegIcon} />
          )}
        </View>
        <View style={styles.orderItemDetails}>
          <Text allowFontScaling={false} style={styles.orderItemName}>{item.name}</Text>
          <View style={styles.orderRowBetween}>
            <Text allowFontScaling={false} style={styles.orderItemQuantity}>
              {t('QTY:')} {item.quantity} {'('}
              {item.price} X {item.quantity}
              {')'}
            </Text>
            <Text allowFontScaling={false} style={styles.orderItemPrice}>
              {t('RUPPEE_SYMBOL')}
              {(Number(item.price) * Number(item.quantity)).toFixed(2)}
            </Text>
          </View>
        </View>
      </View>
    )
}

export default OrderProductItem;


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
  deliveryStatus: {
    fontSize: 12,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.GREEN,
    paddingHorizontal: 14,
    paddingVertical: 5,
    borderRadius: 42,
  },
  orderDateTime: {
    fontSize: 12,
    fontFamily: FONT.REGULAR,
    color: _COL.BLACK,
    marginTop: 2,
  },
  orderItemsContainer: {
    // paddingHorizontal: 16,
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