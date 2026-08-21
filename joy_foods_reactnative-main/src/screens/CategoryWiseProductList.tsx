import { View, Text, TouchableOpacity, StyleSheet, FlatList, RefreshControl, ActivityIndicator, Image } from 'react-native';
import React, { useCallback, useEffect, useState } from 'react';
import { _COL, _H, _HEIGHT, FONT } from 'utils';
import { useT } from 'internationalization';
import InputField from 'components/ui/InputField';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { BACK_BTN_IC, NO_ITEM_IMG, SEARCH_IC } from 'assets';
import { GETreq } from 'api';
import { storeDataAtom } from 'store';
import { useAtom } from 'jotai';
import { ProductListT, StackProps } from 'types';
import { ProductItemView, useLoader } from 'components';
import { decodeHtml } from 'function';

const CategoryWiseProductList = ({ navigation, route: { params: { categoryID, categoryName } } }: StackProps<'CategoryWiseProductList'>) => {

  const { t } = useT();
  const insets = useSafeAreaInsets();
  const [storeData] = useAtom(storeDataAtom);
  const [productList, setProductList] = useState<ProductListT[]>([]);
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const [hasNextPage, setHasNextPage] = useState(false);
  const { showLoader, hideLoader } = useLoader();

  const fetchProducts = useCallback(
    async (pageNumber = 1, refreshing = false) => {
      try {
        if (refreshing) {
          setIsRefreshing(true);
        } else if (pageNumber > 1) {
          setIsLoadingMore(true);
        }

        const isSearch = search.trim().length > 0;
        const endpoint = isSearch ? 'catalog/search' : 'catalog/products';
        const params: any = {
          store_id: storeData?.id,
          module: storeData?.store_type,
          page: pageNumber,
          per_page: 20,
        };

        if (isSearch) {
          params.keyword = search;
          params.category_id = categoryID;
        } else {
          params.category_id = categoryID;
        }

        const { data, success } = await GETreq(endpoint, params);
        console.log('Category Wise Products::', data);

        if (success) {
          const newProducts = data?.data?.products || [];
          const pagination = data?.data?.pagination;

          if (refreshing || pageNumber === 1) {
            setProductList(newProducts);
          } else {
            setProductList(prev => [...prev, ...newProducts]);
          }

          setPage(pageNumber);
          setHasNextPage(pagination?.has_next || false);
        }
      } catch (err) {
        console.log('ERROR::', err);
      } finally {
        setIsRefreshing(false);
        setIsLoadingMore(false);
        hideLoader();
      }
    },
    [categoryID, storeData, search],
  );

  useEffect(() => {
    if (productList.length == 0) {
      showLoader();
    }
    const delayDebounceFn = setTimeout(() => {
      fetchProducts(1, true);
    }, 500);

    return () => clearTimeout(delayDebounceFn);
  }, [search]);

  const onRefresh = () => {
    fetchProducts(1, true);
  };

  const onLoadMore = () => {
    if (hasNextPage && !isLoadingMore && !isRefreshing) {
      fetchProducts(page + 1);
    }
  };

  const renderItem = ({ item }: { item: ProductListT }) => {
    return (
      <ProductItemView
        item={item}
        onItemPress={() =>
          navigation.navigate('ItemDetails', { itemId: item.id ?? 0 })
        }
        onBookPress={() =>
          navigation.navigate('ItemDetails', { itemId: item.id ?? 0 })
        }
        storeData={storeData}
        setItemData={(item: ProductListT) => {
          const updatedProductList = productList?.map(product => {
            if (product?.id === item?.id) {
              return {
                ...product,
                is_in_cart: item.is_in_cart,
                cart_quantity: item.cart_quantity,
                cart_id: item.cart_id,
              };
            }
            return product;
          });
          setProductList(updatedProductList);
        }}
      />
    );
  };

  const renderFooter = () => {
    if (!isLoadingMore) return null;
    return (
      <View style={styles.footerLoader}>
        <ActivityIndicator size="small" color={_COL.PRIMARY_RED} />
      </View>
    );
  };

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
        <Text allowFontScaling={false} style={styles.title}>{decodeHtml(categoryName)}</Text>
      </View>
      <View style={styles.divider} />

      <InputField
        placeholder={t('SEARCH')}
        leftIcon={<SEARCH_IC />}
        value={search}
        onChangeText={text => {
          setSearch(text);
        }}
        isGradient={false}
        containerStyle={styles.inputOuterContainer}
        autoCapitalize="none"
      />

      <View style={styles.popularItemContainer}>
        {/* Popular Items FlatList GridView */}
        <FlatList
          data={productList}
          keyExtractor={item => item.id?.toString() ?? ''}
          numColumns={2}
          showsVerticalScrollIndicator={false}
          contentContainerStyle={styles.popularItemContentContainer}
          renderItem={renderItem}
          refreshControl={
            <RefreshControl
              refreshing={isRefreshing}
              onRefresh={onRefresh}
              colors={[_COL.PRIMARY_RED]}
            />
          }
          onEndReached={onLoadMore}
          onEndReachedThreshold={0.5}
          ListFooterComponent={renderFooter}
          ListEmptyComponent={
            <View style={{ alignItems: 'center', marginTop: _HEIGHT * 0.15 }}>
              <Image source={NO_ITEM_IMG} />
              <Text allowFontScaling={false}
                style={{
                  textAlign: 'center',
                  marginTop: 8,
                  fontFamily: FONT.REGULAR,
                  fontSize: 14,
                  color: _COL.FINAL_BLACK,
                }}
              >
                {t('NO_ITEM_FOUND')}
              </Text>
            </View>
          }
        />
      </View>
    </View>
  );
};

export default CategoryWiseProductList;

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
    bottom: -2,
  },
  title: {
    fontFamily: FONT.SEMI_BOLD,
    fontSize: 20,
    textAlign: 'center',
    flex: 1,
    color: _COL.FINAL_BLACK,
    lineHeight: 24
  },
  divider: {
    height: 1,
    backgroundColor: _COL.BORDER_FOURTH,
    marginTop: 14,
  },
  popularItemContainer: {
    flex: 1,
  },
  popularItemContentContainer: {
    paddingLeft: 16,
    paddingRight: 16,
    paddingBottom: 20,
    marginTop: 10,
  },
  inputOuterContainer: {
    paddingHorizontal: 16,
    paddingTop: 16,
  },
  footerLoader: {
    paddingVertical: 20,
    alignItems: 'center',
  },
});
