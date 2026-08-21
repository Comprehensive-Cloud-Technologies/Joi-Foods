import { CART_IC, DOWN_ARROW_IC, JOI_SPLASH_ICON, SEARCH_IC } from 'assets';
import { useAtom } from 'jotai';
import { memo } from 'react';
import { StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { storeDataAtom } from 'store';
import { _COL, FONT } from 'utils';

const HomeHeader = ({
  onSearch,
  onCart,
  onStoreChange,
  cartItemCount,
}: {
  onSearch?: () => void;
  onCart?: () => void;
  onStoreChange?: () => void;
  cartItemCount?: number;
}) => {
  const [storeData] = useAtom(storeDataAtom);
  return (
    <View style={[styles.headerContainer, styles.row]}>
      <JOI_SPLASH_ICON height={42} width={42} />

      <TouchableOpacity style={{ marginLeft: 12 }} onPress={onStoreChange}>
        <View style={[styles.row, { alignItems: 'center' }]}>
          <Text allowFontScaling={false} style={styles.downtownExpressText}>{storeData?.name}</Text>
          <DOWN_ARROW_IC style={{ marginLeft: 5 }} />
        </View>
        <Text allowFontScaling={false} style={styles.qsrText}>{storeData?.store_type == 'QSR' ?
          storeData?.store_type : storeData.store_type.charAt(0).toUpperCase() + storeData.store_type.slice(1).toLowerCase()}</Text>
      </TouchableOpacity>

      <View style={styles.headerButtonsContainer}>
        <TouchableOpacity
          activeOpacity={0.8}
          onPress={onSearch}
          style={styles.headerButtons}
        >
          <SEARCH_IC style={styles.headerBtn} />
        </TouchableOpacity>
        {(storeData?.store_type == 'QSR' || storeData?.store_type == 'KOT') && (
          <TouchableOpacity
            activeOpacity={0.8}
            onPress={onCart}
            style={styles.headerButtons}
          >
            <CART_IC style={styles.headerBtn} />
            {!!cartItemCount && cartItemCount > 0 &&
              <View style={styles.cartItemCountContainer}>
                <Text allowFontScaling={false} style={styles.cartItemCountText}>{cartItemCount}</Text>
              </View>
            }
          </TouchableOpacity>
        )}
      </View>
    </View>
  );
};

export default memo(HomeHeader);

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.WHITE,
    paddingTop: 5,
  },
  headerContainer: {
    marginLeft: 16,
  },
  row: {
    flexDirection: 'row',
  },
  downTownExpressBtn: {
    flexDirection: 'column',
  },
  downtownExpressText: {
    fontSize: 16,
    fontFamily: FONT.BOLD,
    color: _COL.FINAL_BLACK,
    lineHeight: 24,
  },
  downArrow: {
    marginLeft: 6,
    marginTop: 10,
  },
  qsrText: {
    fontSize: 10,
    fontFamily: FONT.REGULAR,
    color: _COL.MAIN_BLACK,
    marginLeft: 2,
  },
  headerButtonsContainer: {
    flexDirection: 'row',
    marginBottom: 18,
    marginLeft: 'auto',
    marginRight: 16,
    gap: 10,
    alignItems: 'center',
  },
  headerButtons: {
    height: 42,
    width: 42,
    backgroundColor: _COL.WHITE,
    borderRadius: 21,
    borderWidth: 1,
    borderColor: _COL.BORDER_THIRD,
  },
  headerBtn: {
    marginTop: 8,
    alignSelf: 'center',
  },
  cartItemCountContainer: {
    position: 'absolute',
    height: 16,
    minWidth: 16,
    borderRadius: 20,
    backgroundColor: _COL.SECONDARY_ORANGE,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 4,
    top: -2,
    right: -4,
  },
  cartItemCountText: {
    fontSize: 10,
    fontFamily: FONT.MEDIUM,
    color: _COL.WHITE,
  },
});
