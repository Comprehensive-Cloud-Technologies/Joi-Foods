import { View, Text, StyleSheet, Dimensions, TouchableOpacity, FlatList, Image } from 'react-native';
import React, { useCallback, useEffect, useState } from 'react';
import { _COL, _H, FONT, isIOS } from 'utils';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useT } from 'internationalization';
import { BACK_BTN_IC, CLOCK_IC, CLOSE_IC, CLOSE_LARGE_IC, NO_ITEM_IMG } from 'assets';
import { useAtom } from 'jotai';
import { searchHistoryAtom, storeDataAtom } from 'store';
import { GETreq } from 'api';
import { ProductListT } from 'types';
import { InputField, ProductItemView } from 'components';
import { ActivityIndicator } from 'react-native-paper';

const height = Dimensions.get('window').height;

const Search = ({ navigation }: any) => {
  const { t } = useT();
  const insets = useSafeAreaInsets();

  const [search, setSearch] = useState('');
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const [hasNextPage, setHasNextPage] = useState(false);
  const [storeData] = useAtom(storeDataAtom);
  const [productList, setProductList] = useState<ProductListT[]>([]);
  const [page, setPage] = useState(1);

  const [searchHistory, setSearchHistory] = useAtom(searchHistoryAtom);

  const addToHistory = useCallback(
    (keyword: string) => {
      const trimmedKeyword = keyword.trim();
      if (trimmedKeyword.length === 0) return;

      setSearchHistory((prev: string[]) => {
        const filtered = prev.filter((item: string) => item !== trimmedKeyword);
        return [trimmedKeyword, ...filtered].slice(0, 6);
      });
    },
    [setSearchHistory],
  );

  useEffect(() => {
    const delayDebounceFn = setTimeout(() => {
      if (search.trim().length > 0) {
        fetchProducts(1, true);
        addToHistory(search);
      } else {
        setProductList([]);
      }
    }, 800);

    return () => clearTimeout(delayDebounceFn);
  }, [search]);

  const fetchProducts = useCallback(
    async (pageNumber = 1, refreshing = false) => {
      try {
        if (search.trim().length == 0) {
          return;
        }
        if (refreshing) {
          setIsRefreshing(true);
        } else if (pageNumber > 1) {
          setIsLoadingMore(true);
        }

        const params: any = {
          store_id: storeData?.id,
          module: storeData?.store_type,
          page: pageNumber,
          per_page: 20,
          keyword: search,
        };

        const { data, success } = await GETreq('catalog/search', params);
        console.log('data', JSON.stringify(data, null, 3));

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
      }
    },
    [storeData, search],
  );

  const removeAllHistory = () => {
    setSearchHistory([]);
  };

  const haddleRemoveHistoryItem = (keyword: string) => {
    setSearchHistory((prev: string[]) =>
      prev.filter((item: string) => item !== keyword),
    );
  };

  useEffect(() => {
    if (search.trim().length === 0) {
      searchNotFound();
    }
  }, [search]);

  const searchNotFound = () => {
    return (
      <View style={styles.noReacentHistoryContainer}>
        <Image source={NO_ITEM_IMG} />
        <Text allowFontScaling={false} style={styles.noRecentHistoryItemText}>
          {t('NO_HISTORY_FOUND')}
        </Text>
      </View>
    );
  };

  const renderItem = ({ item }: { item: string }) => (
    <TouchableOpacity
      style={[styles.reacentHistoryItem, styles.row]}
      onPress={() => {
        setSearch(item);
        fetchProducts(1, true);
      }}
    >
      <View style={styles.row}>
        <CLOCK_IC />
        <Text allowFontScaling={false} style={styles.reacentHistoryItemText}>{item}</Text>
      </View>
      <TouchableOpacity onPress={() => haddleRemoveHistoryItem(item)}>
        <CLOSE_IC />
      </TouchableOpacity>
    </TouchableOpacity>
  );

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <View
        style={{
          flexDirection: 'row',
          paddingLeft: 16,
          paddingRight: 6,
          alignItems: 'center',
          paddingTop: 8
        }}
      >
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <BACK_BTN_IC />
        </TouchableOpacity>

        <InputField
          placeholder={t('SEARCH')}
          value={search}
          onChangeText={text => setSearch(text)}
          containerStyle={{ flex: 1, marginLeft: 12 }}
          inputContainerStyle={{ borderWidth: 0 }}
          rightIcon={search.trim().length > 0 && <CLOSE_LARGE_IC />}
          onRightIconPress={() => setSearch('')}
        />
      </View>
      <View style={styles.divider} />

      {search.trim().length === 0 ? (
        <>
          <View style={[styles.reacentHistory, styles.row]}>
            <Text allowFontScaling={false} style={styles.reacentHistoryText}>{t('RECENT_HISTORY')}</Text>
            {searchHistory.length > 0 && (
              <TouchableOpacity onPress={removeAllHistory}>
                <Text allowFontScaling={false} style={styles.reacentHistoryTextClearAll}>
                  {t('CLEAR_ALL')}
                </Text>
              </TouchableOpacity>
            )}
          </View>

          {searchHistory.length > 0 ? (
            <FlatList
              data={searchHistory}
              keyExtractor={(item: string) => item}
              renderItem={renderItem}
            />
          ) : (
            <View style={{ alignItems: 'center', marginTop: _H / 6 }}>
              <Image source={NO_ITEM_IMG} />
              <Text allowFontScaling={false} style={styles.noRecentHistoryItemText}>
                {t('NO_RECENT_HISTORY')}
              </Text>
            </View>
          )}
        </>
      ) : (
        <View style={{ flex: 1 }}>
          <FlatList
            data={productList}
            keyExtractor={item =>
              item.id?.toString() ?? Math.random().toString()
            }
            numColumns={2}
            contentContainerStyle={{ paddingHorizontal: 11, paddingBottom: 20 }}
            renderItem={({ item }) => (
              <ProductItemView
                item={item}
                storeData={storeData}
                onItemPress={() =>
                  navigation.navigate('ItemDetails', { itemId: item.id })
                }
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
            )}
            ListEmptyComponent={() =>
              !isRefreshing && (
                <View style={styles.noReacentHistoryContainer}>
                  <Image source={NO_ITEM_IMG} style={{ marginTop: _H / 5 }} />
                  <Text allowFontScaling={false} style={styles.noRecentHistoryItemText}>
                    No results found for your search.
                  </Text>
                </View>
              )
            }
            ListHeaderComponent={() =>
              isRefreshing && (
                <ActivityIndicator
                  color={_COL.SECONDARY_ORANGE}
                  style={{ marginTop: 20 }}
                />
              )
            }
          />
        </View>
      )}
    </View>
  );
};

export default Search;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.WHITE,
    paddingTop: height * 0.01,
  },
  backBtn: {
    marginLeft: 16,
  },
  row: {
    flexDirection: 'row',
  },
  search: {
    fontSize: 14,
    marginLeft: 14,
    fontFamily: FONT.REGULAR,
    color: _COL.TEXT_BLACK,
    top: isIOS ? 2 : 4,
  },
  divider: {
    height: 1,
    width: '100%',
    backgroundColor: _COL.BORDER,
    // marginTop: 12,
  },
  reacentHistory: {
    marginTop: 16,
    justifyContent: 'space-between',
    paddingHorizontal: 16,
  },
  reacentHistoryText: {
    fontSize: 16,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.FINAL_BLACK,
  },
  reacentHistoryTextClearAll: {
    fontSize: 14,
    fontFamily: FONT.MEDIUM,
    color: _COL.TEXT_GREY,
    marginLeft: 'auto',
  },
  reacentHistoryItem: {
    justifyContent: 'space-between',
    marginTop: 10,
    borderBottomWidth: 1,
    borderBottomColor: _COL.TRACK_ONE,
    paddingVertical: 10,
    marginHorizontal: 16,
  },
  reacentHistoryItemText: {
    marginLeft: 8,
    fontSize: 12,
    fontFamily: FONT.REGULAR,
    color: _COL.FINAL_BLACK,
  },
  noReacentHistoryContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    flex: 1,
  },
  noRecentHistoryItemText: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    color: _COL.MAIN_BLACK,
  },
});
