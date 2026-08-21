import { View, StyleSheet, ScrollView, Text, FlatList, Image, TouchableOpacity } from 'react-native';
import React, { useContext, useEffect, useMemo, useState } from 'react';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { _COL, FONT, isIOS } from 'utils';
import { useT } from 'internationalization';
import { HomeBanner, CategoryList, ProductItemView, HomeHeader, TitleWithAllBtn, useLoader } from 'components';
import { GETreq, POSTreq } from 'api';
import { CategoryListT, IBanner, NavProps, ProductListT } from 'types';
import { AppCtx, storeDataAtom } from 'store';
import { SET_CATEGORY_LIST, SET_PRODUCT_LIST } from 'store/context';
import { useAtom } from 'jotai';
import StoreType from 'types/StoreTypes';
import { messaging, requestNotificationPermission } from 'utils/firebase';
import { decodeHtml } from 'function';

const Home = ({ navigation }: NavProps<'Menu'>) => {
  const insets = useSafeAreaInsets();
  const { t } = useT();
  const [banners, setBanners] = useState<IBanner[]>([]);
  const [cartItemCount, setCartItemCount] = useState(0);
  const [storeData] = useAtom(storeDataAtom);
  const { showLoader, hideLoader } = useLoader();
  const {
    dispatch,
    state: { productList, categoryList, cartData },
  } = useContext(AppCtx);

  const handleBannerPress = (banner: IBanner) => {
    if (banner.action?.type === 'CATEGORY' && banner.action?.payload) {
      navigation.navigate('CategoryWiseProductList', {
        categoryID: parseInt(banner.action.payload),
        categoryName: 'Category',
      });
    } else if (banner.action?.type === 'PRODUCT' && banner.action?.payload) {
      navigation.navigate('ItemDetails', {
        itemId: parseInt(banner.action.payload),
      });
    }
  };

  const getBanners = async () => {
    if (StoreType.QSR === storeData.store_type) {
      try {
        const { data, success } = await GETreq('home/banners');
        if (success) {
          setBanners(data?.data?.banners);
        }
      } catch (error) {
        console.log(error);
      }
    }
  };

  const getCategoryData = async () => {
    try {
      showLoader()
      const params = {
        store_id: storeData?.id,
        module: storeData?.store_type,
      };
      console.log('Category Params::', params);
      const { data, success } = await GETreq(StoreType.QSR === storeData.store_type ? 'home/categories' : 'catalog/categories', params);

      if (success) {
        dispatch({
          type: SET_CATEGORY_LIST,
          categoryList: data?.data?.categories,
        });
      }
    } catch (err) {
      console.log('ERROR::', err);
    } finally {
      hideLoader()
    }
  };

  const getProducts = async () => {
    if (StoreType.QSR === storeData.store_type || StoreType.KOT === storeData.store_type) {
      try {
        showLoader();
        const params = {
          store_id: storeData?.id,
          module: storeData?.store_type,
        };
        const { data, success } = await GETreq('home/featured', params);
        console.log('Get Products Data::', JSON.stringify(data, null, 3));
        if (success) {
          dispatch({
            type: SET_PRODUCT_LIST,
            productList: data?.data?.products,
          });
        }
      } catch (err) {
        console.log('ERROR::', err);
      } finally {
        hideLoader()
      }
    }
  };

  const getCartItemCount = async () => {
    if (StoreType.QSR === storeData.store_type || StoreType.KOT === storeData.store_type) {
      try {
        const params = {
          store_id: storeData?.id,
          module: storeData?.store_type,
        }
        const { data, success } =
          await GETreq('cart/count', params);
        if (success) {
          setCartItemCount(data?.data?.count ?? 0);
        } else {
          console.log('Failed to fetch cart item count');
        }
      } catch (error) {
        console.log(error);
      }
    }
  }

  const getFCMToken = async () => {
    try {
      if (isIOS) {
        await messaging().registerDeviceForRemoteMessages();
      }
      const hasPermission = await requestNotificationPermission();
      if (hasPermission) {
        const token = await messaging().getToken();
        console.log('FCM Token::', token);
        const { data, success } = await POSTreq('auth/update_fcm', { fcm_token: token }, true);
        console.log('FCM Token Saved Successfully::', JSON.stringify(data, null, 3));
      } else {
        console.log('Notification permission not granted');
      }
    } catch (error) {
      console.log('FCM Token Save Error::', error);
    }
  }

  useEffect(() => {
    getBanners();
    getCategoryData();
    getProducts();
    getFCMToken();
  }, [storeData.id]);

  useEffect(() => {
    getCartItemCount();
  }, [cartData, productList]);


  const renderItem = ({ item }: { item: ProductListT }) => {
    return (
      <ProductItemView
        item={item}
        onItemPress={() => navigation.navigate('ItemDetails', { itemId: item.id ?? 0 })}
        storeData={storeData}
      />
    );
  };

  const sortedCategories = useMemo(() => {
    if (!categoryList) return [];
    return [...categoryList].sort(
      (a, b) => (a.display_order || 0) - (b.display_order || 0),
    );
  }, [categoryList]);

  const renderPreMealItem = ({ item }: { item: CategoryListT }) => {
    return (
      <TouchableOpacity
        key={item.id}
        style={styles.preMealItems}
        onPress={() => navigation.navigate('CategoryWiseProductList', {
          categoryID: item.id,
          categoryName: item.name,
        })}
      >
        <Image
          source={{ uri: item.thumbnail ?? "" }}
          style={styles.preMealItemImage}
        />
        <Text allowFontScaling={false} style={styles.preMealItemTitle}>{item.name}</Text>
      </TouchableOpacity>
    );
  };

  return (
    <View style={[styles.container, { paddingTop: insets.top + 9 }]}>
      <HomeHeader
        onSearch={() => navigation.navigate('Search')}
        onCart={() => navigation.navigate('MyCart')}
        onStoreChange={() => navigation.navigate('SelectStoreScr', { isFromStoreChange: true })}
        cartItemCount={cartItemCount}
      />

      {StoreType.QSR === storeData.store_type || StoreType.KOT === storeData.store_type ? (
        <ScrollView
          showsVerticalScrollIndicator={false}
          contentContainerStyle={styles.mainScrollContent}
          nestedScrollEnabled={true}
        >
          {banners.length > 0 && storeData.store_type === StoreType.QSR ? (
            <HomeBanner banners={banners} onBannerPress={handleBannerPress} />
          ) : null}

          <CategoryList
            sortedCategories={sortedCategories}
            onViewAllPress={() => navigation.navigate('CategoryList')}
            onCategoryPress={item => {
              navigation.navigate('CategoryWiseProductList', {
                categoryID: item.id,
                categoryName: decodeHtml(item.name),
              });
            }}
            storeData={storeData}
          />

          <TitleWithAllBtn
            title={t('POPULAR_ITEMS')}
            onPress={() => navigation.navigate('CategoryWiseProductList', {
              categoryID: productList?.[0]?.category?.id ?? 0,
              categoryName: 'Popular Items',
            })}
            viewAllText={t('VIEW_ALL')}
          />
          <FlatList
            data={productList?.slice(0, 6)}
            keyExtractor={item => item.id?.toString() ?? ''}
            numColumns={2}
            scrollEnabled={false}
            showsVerticalScrollIndicator={false}
            contentContainerStyle={styles.popularItemContentContainer}
            columnWrapperStyle={styles.popularItemColumnWrapper}
            renderItem={renderItem}
          />
        </ScrollView>
      ) : (
        <FlatList
          data={sortedCategories}
          keyExtractor={item => item.id?.toString() ?? ''}
          renderItem={renderPreMealItem}
          showsVerticalScrollIndicator={false}
          contentContainerStyle={styles.preMealItemContainer}
        />
      )}
    </View>
  );
};

export default Home;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.WHITE,
    paddingTop: 5,
  },
  mainScrollContent: {
    paddingBottom: 20,
  },
  popularItemContentContainer: {
    paddingBottom: 20,
    marginTop: 10,
  },
  popularItemColumnWrapper: {
    paddingHorizontal: 10,
  },
  preMealItems: {
    alignItems: 'center',
    marginBottom: 16,
    borderWidth: 1,
    borderColor: _COL.BORDER,
    borderRadius: 12,
  },
  preMealItemContainer: {
    marginHorizontal: 16,
    borderRadius: 12,
    marginBottom: 20,
  },
  preMealItemImage: {
    width: "100%",
    height: 210,
    borderRadius: 12,
    resizeMode: "cover",
  },
  preMealItemTitle: {
    alignSelf: "flex-start",
    marginLeft: 25,
    marginTop: 21,
    marginBottom: 15,
    fontSize: 22,
    fontFamily: FONT.SEMI_BOLD,
  },
});
