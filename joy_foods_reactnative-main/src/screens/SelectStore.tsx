import { View, Text, TouchableOpacity, StyleSheet, ScrollView, Image, Linking } from 'react-native';
import React, { useEffect, useMemo, useState } from 'react';
import { useAtom } from 'jotai';
import { storeDataAtom } from 'store';
import { _COL, _HEIGHT, _W, _WIDTH, FONT } from 'utils';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { FlatList } from 'react-native-gesture-handler';
import { LinearGradient } from 'react-native-linear-gradient';
import { useT } from 'internationalization';
import { BACK_BTN_IC, SCAN_QR_IC } from 'assets';
import { GETreq } from 'api';
import { IStoreData, StackProps } from 'types';
import { BTN, useLoader, useSnackbar } from 'components';
import { useCameraPermission } from 'react-native-vision-camera';
import { Alert } from 'react-native';

const SelectStore = ({ navigation, route: { params } }: StackProps<'SelectStoreScr' | 'SelectStore'>) => {
  const { t } = useT();
  const insets = useSafeAreaInsets();
  const { hasPermission, requestPermission } = useCameraPermission();
  const [activeCategory, setActiveCategory] = useState('ALL');
  const [storeData, setStoreData] = useAtom(storeDataAtom);
  const [selectedItemId, setSelectedItemId] = useState<number>(storeData?.id);
  const [storeDataList, setStoreDataList] = useState<IStoreData[]>([]);
  const { showLoader, hideLoader } = useLoader();
  const { showSnackbar } = useSnackbar();

  const categories = useMemo(() => {
    const uniqueTypes = Array.from(
      new Set(storeDataList.map((s: IStoreData) => s.store_type)),
    ).filter(Boolean);
    return ['ALL', ...uniqueTypes];
  }, [storeDataList]);

  const filteredStores = useMemo(() => {
    if (activeCategory === 'ALL') {
      return storeDataList;
    }
    return storeDataList.filter(s => s.store_type === activeCategory);
  }, [storeDataList, activeCategory]);

  const getStoreData = async () => {
    try {
      showLoader();
      const { success, data } = await GETreq('stores/get_stores');
      if (success) {
        setStoreDataList(data?.data?.stores);
      }
    } catch (error) {
      console.log('error::', error);
    } finally {
      hideLoader();
    }
  };

  useEffect(() => {
    getStoreData();
  }, []);

  const handleScanQR = async () => {
    let permissionGranted = hasPermission;

    if (!permissionGranted) {
      permissionGranted = await requestPermission();
    }

    if (permissionGranted) {
      if (params?.isFromStoreChange) {
        navigation.navigate('ScanQRCode', { isFromStoreChange: params?.isFromStoreChange });
      } else {
        navigation.navigate('ScanQR', { isFromStoreChange: params?.isFromStoreChange });
      }
    } else {
      Alert.alert(
        t('CAMERA_PERMISSION'),
        t('PLEASE_ALLOW_CAMERA_PERMISSION_FROM_SETTINGS'),
        [
          {
            text: t('CANCEL'),
            style: 'cancel',
          },
          {
            text: t('OPEN_SETTINGS'),
            onPress: () => Linking.openSettings(),
          },
        ]
      );
    }
  };

  const handleContinue = () => {
    if (selectedItemId !== 0) {
      const selectedStore = storeDataList.find(s => s.id === selectedItemId);
      if (selectedStore) {
        setStoreData(selectedStore);
        if (params?.isFromStoreChange) {
          navigation.goBack()
        }
      }
    } else {
      showSnackbar(t('PLEASE_SELECT_A_STORE'));
    }
  };

  const renderItem = ({ item }: { item: IStoreData }) => (
    <TouchableOpacity
      activeOpacity={0.85}
      onPress={() => setSelectedItemId(item.id)}
      style={[
        styles.itemContainer,
        selectedItemId === item.id && styles.itemSelected,
      ]}
    >
      <View style={styles.row}>
        {item.thumbnail ? (
          <Image source={{ uri: item.thumbnail }} style={styles.itemImage} />
        ) : (
          <View
            style={[styles.itemImage, { backgroundColor: _COL.LAYOUT_BG }]}
          />
        )}
        <View style={{ justifyContent: 'space-between', paddingHorizontal: 12, paddingVertical: 4 }}>
          <Text style={styles.itemName} numberOfLines={2} allowFontScaling={false}>
            {item.name}
          </Text>
          <Text style={styles.itemAddress} numberOfLines={2} ellipsizeMode='tail' allowFontScaling={false}>
            {`${item.address.line1} ${item.address.city} ${item.address.state}`.trim()}
          </Text>
        </View>
        <View style={styles.itemCategory}>
          <Text style={styles.itemCategoryText} allowFontScaling={false}>{item.store_type}</Text>
        </View>
      </View>
    </TouchableOpacity>
  );

  return (
    <View style={styles.container}>
      <View style={[styles.headerContainer, { paddingTop: insets.top + 12 }]}>
        {params?.isFromStoreChange ? (
          <TouchableOpacity style={styles.backBtn} onPress={() => navigation.goBack()}>
            <BACK_BTN_IC />
          </TouchableOpacity>
        ) : (
          <View />
        )}

        <TouchableOpacity style={styles.scanQRBtn} onPress={handleScanQR}>
          <View style={styles.row}>
            <SCAN_QR_IC />
            <Text style={styles.scanQRText} allowFontScaling={false}>{t('SCAN_QR')}</Text>
          </View>
        </TouchableOpacity>
      </View>

      <FlatList
        data={filteredStores}
        keyExtractor={item => item.id.toString()}
        renderItem={renderItem}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.flatListContent}
        ListHeaderComponent={
          <>
            <View style={styles.titleContainer}>
              <Text style={styles.title} allowFontScaling={false}>{t('SELECT_YOUR_STORE')}</Text>
              <Text style={styles.subtitle} allowFontScaling={false}>{t('CHOOSE_STORE_OUTLET')}</Text>
            </View>

            <View style={styles.divider} />

            <View style={styles.categoryContainer}>
              <ScrollView horizontal showsHorizontalScrollIndicator={false}>
                {categories.map((item: string) => {
                  return (
                    <TouchableOpacity
                      key={item}
                      activeOpacity={0.8}
                      onPress={() => setActiveCategory(item)}
                      style={[
                        styles.categoryBtn,
                        {
                          backgroundColor:
                            activeCategory === item
                              ? _COL.PRIMARY_RED
                              : undefined,
                          borderColor:
                            activeCategory === item
                              ? _COL.TRANSPARENT
                              : _COL.BORDER,
                        },
                      ]}
                    >
                      <Text
                        style={
                          activeCategory === item
                            ? styles.activeCategoryText
                            : styles.categoryText
                        }
                        allowFontScaling={false}
                      >
                        {t(item as any)}
                      </Text>
                    </TouchableOpacity>
                  );
                })}
              </ScrollView>
            </View>
          </>
        }
      />

      <LinearGradient
        colors={[_COL.WHITE_0, _COL.WHITE]}
        style={[
          styles.linearGradientContainer,
          { height: insets.bottom + 120 },
        ]}
        start={{ x: 0, y: 0 }}
        end={{ x: 0, y: 1 }}
        pointerEvents="box-none"
      >
        <BTN
          title={t('CONTINUE')}
          onP={handleContinue}
          borderR={120}
          width={_WIDTH - 32}
          ctr
          mTop={50}
        />
      </LinearGradient>
    </View>
  );
};

export default SelectStore;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.WHITE,
  },
  headerContainer: {
    backgroundColor: _COL.WHITE,
    paddingHorizontal: _WIDTH * 0.04,
    paddingBottom: 10,
    zIndex: 10,
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  row: {
    flexDirection: 'row',
  },
  column: {
    flexDirection: 'column',
  },
  scanQRBtn: {
    paddingVertical: 5,
    paddingRight: 14,
    paddingLeft: 11,
    borderRadius: 66,
    alignSelf: 'flex-end',
    backgroundColor: _COL.MAIN_BLACK,
  },
  scanQRText: {
    fontSize: 14,
    marginLeft: 5,
    fontFamily: FONT.MEDIUM,
    color: _COL.WHITE,
    textAlign: 'center',
  },
  title: {
    fontSize: 24,
    fontFamily: FONT.BOLD,
    color: _COL.FINAL_BLACK,
  },
  subtitle: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    color: _COL.MAIN_BLACK,
  },
  flatListContent: {
    paddingHorizontal: _WIDTH * 0.04,
    paddingBottom: 220,
  },
  titleContainer: {
    marginTop: _HEIGHT * 0.02,
  },
  divider: {
    height: 1,
    backgroundColor: _COL.DIVIDER,
    marginVertical: 18,
  },
  categoryContainer: {
    marginBottom: 18,
  },
  categoryBtn: {
    marginRight: 10,
    paddingHorizontal: 16,
    paddingVertical: 4,
    borderRadius: 30,
    backgroundColor: _COL.WHITE,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: _COL.BORDER,
  },
  activeCategoryBtn: {
    marginRight: 10,
    paddingHorizontal: 16,
    paddingVertical: 4,
    borderRadius: 30,
    backgroundColor: _COL.PRIMARY_RED,
    justifyContent: 'center',
    alignItems: 'center',
  },
  activeCategoryText: {
    fontSize: 14,
    fontFamily: FONT.MEDIUM,
    color: _COL.WHITE,
    lineHeight: 24,
  },
  categoryText: {
    fontSize: 14,
    fontFamily: FONT.MEDIUM,
    color: _COL.TEXT_GREY_LIGHT,
    lineHeight: 24,
  },
  itemContainer: {
    borderRadius: 16,
    // height: 108,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: _COL.BORDER_EIGHTH,
    padding: 16,
  },
  itemImage: {
    width: 76,
    height: 76,
    borderRadius: 8,
    // marginRight: 12,
    resizeMode: 'cover'
  },
  backBtn: {
    // position: 'absolute',
    // left: 16,
    // zIndex: 1,
    // bottom:12
  },
  itemName: {
    fontSize: 18,
    fontFamily: FONT.MEDIUM,
    color: _COL.FINAL_BLACK,
    width: _W * 0.4,
    // height: 55,
    lineHeight: 21,
  },
  itemAddress: {
    fontSize: 12,
    fontFamily: FONT.REGULAR,
    color: _COL.TEXT_GREY_LIGHT,
    maxWidth: _W * 0.5,
  },
  itemCategory: {
    paddingVertical: 4,
    paddingHorizontal: 12,
    backgroundColor: _COL.LAYOUT_BG,
    borderRadius: 52,
    position: 'absolute',
    right: 0,
    top: 0,
    alignItems: 'center',
    justifyContent: 'center',
  },
  itemCategoryText: {
    fontSize: 12,
    fontFamily: FONT.MEDIUM,
    color: _COL.TEXT_GREY,
    textAlign: 'center',
  },
  itemSelected: {
    borderWidth: 1,
    borderColor: _COL.PRIMARY_RED,
    backgroundColor: _COL.WHITE,
  },
  linearGradientContainer: {
    width: _WIDTH,
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
  },
});
