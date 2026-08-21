import { MINUS_IC, NON_VEG_IC, PLUS_IC, VEG_IC } from 'assets';
import { useT } from 'internationalization';
import { Image, Pressable, StyleProp, StyleSheet, Text, TouchableOpacity, View, ViewStyle } from 'react-native';
import { ICartProduct } from 'types';
import { _COL, _W, FONT } from 'utils';

const CartItemView = ({
  item,
  handleQuantityPlus,
  handleQuantityMinus,
  vSty,
}: {
  item: ICartProduct;
  handleQuantityPlus: (id: number) => void;
  handleQuantityMinus: (quantity: number, id: number) => void;
  vSty?: StyleProp<ViewStyle>;
}) => {
  const { t } = useT();

  return (
    <Pressable>
      <View style={[styles.myCartWarapper, vSty]}>
        <View style={{ flexDirection: 'row', alignItems: 'center' }}>
          <View>
            <Image
              source={{ uri: item.product?.thumbnail ?? item?.thumbnail }}
              style={styles.myCartImage}
            />
            <View style={{ position: 'absolute', right: 6, top: 6 }}>
              {(item.product?.is_vegetarian ?? item?.is_vegetarian) ? (
                <VEG_IC height={18} width={18} />
              ) : (
                <NON_VEG_IC height={18} width={18} />
              )}
            </View>
          </View>
          <View style={{ paddingLeft: 12, flex: 1 }}>
            <Text allowFontScaling={false} style={styles.myCartItemName}>{item.product?.name ?? item?.product_name}</Text>
            <Text allowFontScaling={false}
              style={[styles.inStock, { lineHeight: 16, color: (item.product?.is_in_stock ?? item?.is_in_stock) ? _COL.GREEN : _COL.RED }]}
            >
              {(item.product?.is_in_stock ?? item?.is_in_stock) ? t('IN_STOCK') : t('OUT_OF_STOCK')}
            </Text>

            <View style={{ flexDirection: 'row', justifyContent: 'space-between' }}>
              <View style={{ flexDirection: 'row', flex: 1, }}>
                <Text allowFontScaling={false} style={styles.myCartItemTotalPrice}>
                  ₹{((item?.unit_price ?? 0) * (item?.quantity ?? 0)).toFixed(2)}
                </Text>
                <Text allowFontScaling={false} style={styles.myCartItemPrice}>
                  {'('}
                  {(item?.unit_price ?? 0).toFixed(2)} x {item?.quantity}
                  {')'}
                </Text>
              </View>
              <View style={[styles.quantityContainer, { flexDirection: 'row', width: 100, justifyContent: 'space-between' }]}>
                <TouchableOpacity
                  onPress={() =>
                    handleQuantityMinus(item?.quantity ?? 0, item.cart_id ?? 0)
                  }
                  style={styles.myCartItemQuantityMinusBtn}
                >
                  <MINUS_IC width={11} />
                </TouchableOpacity>
                <Text allowFontScaling={false} style={styles.myCartItemQuantity}>{item?.quantity ?? 0}</Text>
                <TouchableOpacity
                  onPress={() => handleQuantityPlus(item?.cart_id ?? 0)}
                  style={styles.myCartItemQuantityPlusBtn}
                >
                  <PLUS_IC height={12} width={12} />
                </TouchableOpacity>
              </View>
            </View>
          </View>
        </View>


      </View>
    </Pressable>
  );
};

export default CartItemView;

const styles = StyleSheet.create({
  inStock: {
    fontFamily: FONT.MEDIUM,
    fontSize: 10,
    color: _COL.GREEN,
    marginTop: 4,
  },
  quantityContainer: {
    // gap: 16,
    bottom: 5,
    justifyContent: 'center',
  },
  myCartItemQuantityMinusBtn: {
    borderRadius: 6,
    borderWidth: 1,
    borderColor: _COL.TEXT_GREY,
    backgroundColor: _COL.WHITE,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 9,
  },
  myCartItemQuantityPlusBtn: {
    borderRadius: 6,
    borderWidth: 1,
    borderColor: _COL.SECONDARY_ORANGE,
    backgroundColor: _COL.SECONDARY_ORANGE,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 9,
  },
  myCartItemQuantity: {
    fontFamily: FONT.MEDIUM,
    fontSize: 14,
    color: _COL.FINAL_BLACK,
    marginVertical: 4,
  },

  myCartWarapper: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    justifyContent: 'space-between',
    backgroundColor: _COL.WHITE,
  },
  myCartImage: {
    width: 80,
    height: 80,
    borderRadius: 12,
  },
  myCartItemName: {
    fontFamily: FONT.MEDIUM,
    fontSize: 14,
    color: _COL.MAIN_BLACK,
    lineHeight: 20
  },
  myCartItemTotalPrice: {
    fontFamily: FONT.SEMI_BOLD,
    fontSize: 16,
    color: _COL.FINAL_BLACK,
    marginTop: 4,
  },
  myCartItemPrice: {
    fontFamily: FONT.MEDIUM,
    fontSize: 12,
    color: _COL.TEXT_GREY_LIGHT,
    marginTop: 6,
    marginLeft: 8,
  },
});
