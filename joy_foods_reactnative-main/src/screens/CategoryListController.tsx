import { View, Text, TouchableOpacity, StyleSheet, FlatList, Image } from 'react-native';
import React, { useEffect, useState } from 'react';
import { _COL, _H, _W, FONT } from 'utils/constants';
import { useT } from 'internationalization';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { BACK_BTN_IC } from 'assets';
import { storeDataAtom } from 'store';
import { CategoryListT, StackProps } from 'types';
import { GETreq } from 'api';
import { useAtom } from 'jotai';
import { useLoader } from 'components';
import { decodeHtml } from 'function';

const ITEM_WIDTH = _W / 3 - 19;

const CategoryList = ({ navigation }: StackProps<"CategoryList">) => {
  const { t } = useT();
  const [categoryList, setCategoryList] = useState<CategoryListT[]>([]);
  const [storeData] = useAtom(storeDataAtom);
  const insets = useSafeAreaInsets();
  const { showLoader, hideLoader } = useLoader();

  const getCategoryData = async () => {
    try {
      showLoader();
      const params = {
        store_id: storeData?.id,
        module: storeData?.store_type,
      };
      const { data, success } = await GETreq('catalog/categories', params);
      if (success) {
        setCategoryList(data?.data?.categories);
      }
    } catch (err) {
      console.log('ERROR::', err);
    } finally {
      hideLoader();
    }
  };

  useEffect(() => {
    getCategoryData();
  }, [])

  return (
    <View style={[styles.container, { paddingTop: insets.top + 12 }]}>
      <View style={{ flexDirection: 'row' }}>
        <TouchableOpacity
          style={styles.backBtn}
          onPress={() => {
            navigation.goBack();
          }}
        >
          <BACK_BTN_IC />
        </TouchableOpacity>

        <Text style={styles.title} allowFontScaling={false}>{t('CATEGORY')}</Text>
      </View>
      <View style={styles.divider} />

      <View style={styles.categoryContainer}>
        <FlatList
          data={categoryList}
          keyExtractor={item => item.id.toString()}
          numColumns={3}
          columnWrapperStyle={styles.columnWrapper}
          contentContainerStyle={styles.contentContainer}
          renderItem={({ item }) => (
            <TouchableOpacity
              style={styles.categoryItem}
              activeOpacity={0.8}
              onPress={() => {
                navigation.navigate('CategoryWiseProductList', {
                  categoryID: item.id,
                  categoryName: item.name,
                });
              }}
            >
              <View
                style={[
                  styles.categoryImageWrapper,
                  {
                    width: ITEM_WIDTH * 0.6,
                    height: ITEM_WIDTH * 0.6,
                    borderRadius: (ITEM_WIDTH * 0.6) / 2,
                  },
                ]}
              >
                <Image
                  source={{ uri: item.icon }}
                  style={styles.categoryImage}
                />
              </View>
              <Text style={styles.categoryName} allowFontScaling={false}>{decodeHtml(item.name)}</Text>
            </TouchableOpacity>
          )}
        />
      </View>
    </View>
  );
};

export default CategoryList;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.WHITE,
    paddingTop: _H * 0.01,
  },
  backBtn: {
    position: 'absolute',
    left: 16,
    zIndex: 10,
    bottom: -2
  },
  title: {
    fontFamily: FONT.SEMI_BOLD,
    fontSize: 20,
    flex: 1,
    textAlign: 'center',
    color: _COL.FINAL_BLACK,
    lineHeight: 26,
  },
  divider: {
    height: 1,
    backgroundColor: _COL.BORDER_FOURTH,
    marginTop: 14,
  },
  categoryContainer: {
    flex: 1,
    paddingHorizontal: 16,
  },
  categoryItem: {
    alignItems: 'center',
    backgroundColor: _COL.WHITE,
    borderColor: _COL.BORDER,
    marginBottom: 12,
    borderWidth: 1,
    borderRadius: 17,
    paddingVertical: 12,
    width: ITEM_WIDTH
  },
  categoryImageWrapper: {
    backgroundColor: _COL.WHITE,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 3,
  },
  categoryImage: {
    width: 52,
    height: 52,
    resizeMode: 'contain',
    borderRadius: 52
  },
  categoryName: {
    fontFamily: FONT.REGULAR,
    fontSize: 14,
    color: _COL.FINAL_BLACK,
    textAlign: 'center',
  },
  columnWrapper: {
    gap: 12,
  },
  contentContainer: {
    marginTop: 17,
    paddingBottom: 16,
  },
});
