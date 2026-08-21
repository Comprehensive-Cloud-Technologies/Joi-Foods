import { CART2_IC, MINUS_IC, NON_VEG_IC, PLACEHOLDER_IMG, PLUS_IC, VEG_IC } from 'assets';
import ConfirmationAlert from 'components/ConfirmationAlert';
import { useAddMinusCart } from 'hooks';
import { useT } from 'internationalization';
import { memo } from 'react';
import { Image, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { IStoreData, ProductListT } from 'types';
import StoreType from 'types/StoreTypes';
import { _COL, FONT } from 'utils';

const ProductItemView = ({
  item,
  onItemPress,
  storeData,
  setItemData,
  onBookPress,
}: {
  item: ProductListT;
  onItemPress?: () => void;
  onBookPress?: () => void;
  storeData?: IStoreData;
  setItemData?: (item: ProductListT) => void;
}) => {
  const { t } = useT();
  const { handleAddToCart, handleIncrement, handleDecrement, alertRef } = useAddMinusCart();

  return (
    <TouchableOpacity
      onPress={onItemPress}
      activeOpacity={0.8}
      style={styles.popularItemWrapper}
    >
      {item.thumbnail ? (
        <Image
          source={{ uri: item.thumbnail }}
          style={styles.popularItemImage}
        />
      ) : (
        <Image source={PLACEHOLDER_IMG} style={styles.popularItemImage} />
      )}

      {item.is_vegetarian ? (
        <VEG_IC style={styles.isVegIcon} />
      ) : (
        <NON_VEG_IC style={styles.isVegIcon} />
      )}

      {/* {StoreType.PREMEAL === storeData?.store_type
        && (
            <View style={styles.isPaidByCompanyBadge}>
              <Text allowFontScaling={false} style={styles.isPaidByCompanyText}>
                {item.percentOffByCompany}% {t('PAID_BY_COMPANY')}
              </Text>
            </View>
          )} */}

      <View style={{ padding: 12 }}>
        <Text
          style={styles.popularItemName}
          ellipsizeMode="tail"
          numberOfLines={1}
          allowFontScaling={false}
        >
          {item.name}
        </Text>

        {(StoreType.QSR === storeData?.store_type || StoreType.KOT === storeData?.store_type) &&
          <Text allowFontScaling={false} style={[styles.inStock, { color: item.is_in_stock ? _COL.GREEN : _COL.RED }]}>{item.is_in_stock ? t('IN_STOCK') : t('OUT_OF_STOCK')}</Text>
        }

        <View
          style={{
            flexDirection: 'row',
            justifyContent: 'space-between',
            alignItems: 'center',
            flexWrap: 'wrap',
            maxWidth: "95%",
            marginTop: StoreType.PREMEAL === storeData?.store_type ? 6 : 0,
          }}
        >
          <Text allowFontScaling={false} style={styles.popularItemPrice}>₹{item.price?.toFixed(2)}</Text>

          {StoreType.PREMEAL === storeData?.store_type ? (
            <TouchableOpacity style={styles.bookBtn} onPress={onBookPress}>
              <Text allowFontScaling={false} style={styles.bookBtnText}>{t('BOOK')}</Text>
            </TouchableOpacity>
          ) : !item.is_in_cart ? (
            <TouchableOpacity
              style={[
                styles.addToCartButton,
                { opacity: item.is_in_stock ? 1 : 0.5 },
              ]}
              onPress={() => {
                handleAddToCart(item.id?.toString() ?? '', cartId => {
                  setItemData?.({
                    ...item,
                    is_in_cart: true,
                    cart_quantity: 1,
                    cart_id: cartId,
                  });
                });
              }}
              disabled={!item.is_in_stock}
            >
              <CART2_IC />
            </TouchableOpacity>
          ) : (
            <View
              style={{ flexDirection: 'row', alignItems: 'center', gap: 10 }}
            >
              <TouchableOpacity
                style={{
                  borderWidth: 1,
                  borderRadius: 6,
                  padding: 9,
                  paddingVertical: 13,
                }}
                onPress={() =>
                  handleDecrement(item.cart_id?.toString() ?? '', quantity => {
                    setItemData?.({
                      ...item,
                      cart_quantity: quantity,
                      is_in_cart: quantity > 0,
                    });
                  })
                }
              >
                <MINUS_IC />
              </TouchableOpacity>
              <Text allowFontScaling={false}
                style={{
                  fontSize: 14,
                  fontFamily: FONT.BOLD,
                  color: _COL.FINAL_BLACK,
                  maxWidth: 28,
                }}
                numberOfLines={1}
                ellipsizeMode="tail"
              >
                {item.cart_quantity}
              </Text>
              <TouchableOpacity
                style={{
                  backgroundColor: _COL.SECONDARY_ORANGE,
                  padding: 9,
                  borderRadius: 6,
                }}
                onPress={() =>
                  handleIncrement(item.cart_id?.toString() ?? '', quantity => {
                    setItemData?.({
                      ...item,
                      cart_quantity: quantity,
                    });
                  })
                }
              >
                <PLUS_IC />
              </TouchableOpacity>
            </View>
          )}
        </View>
      </View>
      <ConfirmationAlert ref={alertRef} onConfirm={() => { }} t={t} />
    </TouchableOpacity>
  );
};

export default memo(ProductItemView);

const styles = StyleSheet.create({
  popularItemWrapper: {
    flex: 1,
    marginHorizontal: 5,
    marginVertical: 5,
    borderRadius: 12,
    backgroundColor: _COL.WHITE,
    borderWidth: 1,
    borderColor: _COL.BORDER_FOURTH,
    maxWidth: '48%',
  },
  popularItemImage: {
    width: '100%',
    height: 175,
    borderRadius: 12,
    resizeMode: 'cover',
  },
  popularItemName: {
    fontSize: 14,
    fontFamily: FONT.MEDIUM,
    color: _COL.MAIN_BLACK,
    lineHeight: 18,
  },
  inStock: {
    marginTop: 3,
    fontSize: 10,
    fontFamily: FONT.MEDIUM,
    color: _COL.GREEN,
  },
  popularItemPrice: {
    fontSize: 14,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.FINAL_BLACK,
  },
  isVegIcon: {
    position: 'absolute',
    top: 8,
    right: 8,
  },
  addToCartButton: {
    borderRadius: 16,
    padding: 7,
    backgroundColor: _COL.SECONDARY_ORANGE,
    justifyContent: 'center',
    alignItems: 'center',
  },
  bookBtn: {
    backgroundColor: _COL.PRIMARY_RED,
    paddingVertical: 4,
    paddingHorizontal: 14,
    borderRadius: 54,
  },
  bookBtnText: {
    color: _COL.WHITE,
    fontSize: 14,
    fontFamily: FONT.SEMI_BOLD,
    lineHeight: 20,
  },
  isPaidByCompanyBadge: {
    position: 'absolute',
    bottom: 75,
    left: 0,
    right: 0,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: _COL.SECONDARY_ORANGE,
    paddingHorizontal: 15,
    paddingVertical: 8,
    borderBottomRightRadius: 12,
    borderBottomLeftRadius: 12,
  },
  isPaidByCompanyText: {
    color: _COL.WHITE,
    fontSize: 12,
    fontFamily: FONT.SEMI_BOLD,
  },
});
