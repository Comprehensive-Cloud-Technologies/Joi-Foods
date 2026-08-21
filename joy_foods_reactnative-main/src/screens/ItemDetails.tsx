import React, { useMemo } from 'react';
import { View, Text, ScrollView, ImageBackground, TouchableOpacity, Image } from 'react-native';
import Animated, { LinearTransition as Layout, FadeIn, FadeOut } from 'react-native-reanimated';
import { _COL, _H, _HEIGHT, _WIDTH } from 'utils';
import { BACK_ARROW_IC, CALENDER_IC, DESCRIPTION_IC, DOWN_ARROW, INGREDIENTS_IC, MINUS_IC, MY_CART_IC, NO_MENU_IMG, NON_VEG_IC, PLUS_IC, REFUND_IC, VEG_IC } from 'assets';
import { StackProps } from 'types';
import { BTN, ConfirmationAlert, ScheduleCalendarSheet } from 'components';
import LinearGradient from 'react-native-linear-gradient';
import { useItemDetails } from 'hooks';
import StoreType from 'types/StoreTypes';
import { formatTimeWithAmPm } from 'function';
import { itemDetailsSty } from 'styles';

export default function ItemDetails(Props: StackProps<'ItemDetails'>) {

  const { productDetails, t, navigation, contentHeight, expandedIds, handleAddToCart, handleLayout, handleOrderNow, handlePremealOrderNow,
    handleScheduleCheck, headerBgStyle, imageAnimatedStyle, isBooking, onScroll, page, scrollHandler, setIsBooking, titleAnimatedStyle,
    toggleExpand, storeData, IMAGE_HEIGHT, bottom, error, handleDecrement, handleIncrement, isFromMyCart, scrollRef, top,
    cartItemCount, alertRef
  } = useItemDetails(Props);

  const detailsItems = useMemo(() => {
    const items = [];
    if (productDetails?.description) {
      items.push({
        id: 1,
        title: t('DESCRIPTION'),
        image: <DESCRIPTION_IC />,
        content: productDetails.description,
      });
    }
    if (productDetails?.ingredients) {
      items.push({
        id: 2,
        title: t('INGREDIENTS'),
        image: <INGREDIENTS_IC />,
        ingredients: productDetails.ingredients
          .split(',')
          .map(i => i.trim())
          .filter(i => i !== ''),
      });
    }
    if (StoreType.PREMEAL === storeData?.store_type) {
      items.push({
        id: 3,
        title: t('MEAL_SCHEDULE'),
        image: <CALENDER_IC />,
        menu: productDetails?.weekly_schedule?.schedule?.map(day => ({
          day: day.day,
          available: day.available,
          display_order: day.display_order,
          menu_items: day.menu_items?.items?.map(items => ({
            item: items
          })),
        })),
      });
    }
    items.push({
      id: 4,
      title: t('REFUND_CANCEL'),
      image: <REFUND_IC />,
      content:
        'You can request a refund or cancel your order within 30 minutes of placing it. Please contact our support team for assistance.',
    });
    return items;
  }, [productDetails, t, StoreType.QSR === storeData?.store_type]);

  return (
    <View style={itemDetailsSty.container} onLayout={handleLayout}>
      <Animated.ScrollView
        onScroll={scrollHandler}
        scrollEventThrottle={16}
        contentContainerStyle={{ paddingTop: IMAGE_HEIGHT }}
        showsVerticalScrollIndicator={false}
        bounces={false}
        overScrollMode="never"
        scrollEnabled={contentHeight + IMAGE_HEIGHT > _HEIGHT}
      >
        <View style={[itemDetailsSty.content, { paddingHorizontal: 16 }]} onLayout={handleLayout}>
          <View style={itemDetailsSty.itemContainer}>
            <View style={[itemDetailsSty.row, { justifyContent: 'space-between' }]}>
              <Text allowFontScaling={false} style={itemDetailsSty.animatedTitle} numberOfLines={1}>
                {productDetails?.name}
              </Text>
              {productDetails?.is_vegetarian ? (
                <VEG_IC style={itemDetailsSty.vegNonVegIcon} />
              ) : (
                <NON_VEG_IC style={itemDetailsSty.vegNonVegIcon} />
              )}
            </View>

            {StoreType.PREMEAL === storeData?.store_type ? (
              <Text allowFontScaling={false} style={itemDetailsSty.orderForTodayBefore}>
                {t('ORDER_FOR_TODAY_BEFORE')}
                <Text allowFontScaling={false} style={itemDetailsSty.orderBeforeTime}> {formatTimeWithAmPm(productDetails?.premeal_info?.cutoff_time ?? "")}</Text>
              </Text>
            ) : productDetails?.is_in_stock ? (
              <Text allowFontScaling={false} style={itemDetailsSty.itemIsStock}>{t('IN_STOCK')}</Text>
            ) : (
              <Text allowFontScaling={false} style={itemDetailsSty.itemIsOutOfStock}>{t('OUT_OF_STOCK')}</Text>
            )}
          </View>

          <View style={itemDetailsSty.totalPriceContainer}>
            <View style={itemDetailsSty.secondRow}>
              <Text allowFontScaling={false} style={itemDetailsSty.myCartItemTotalPrice}>
                ₹
                {((productDetails?.cart_quantity ?? 0) > 0
                  ? (productDetails?.price ?? 0) *
                  (productDetails?.cart_quantity ?? 0)
                  : productDetails?.price ?? 0
                ).toFixed(2)}
              </Text>

              {StoreType.PREMEAL !== storeData?.store_type
                && productDetails?.is_in_cart && (
                  <Text allowFontScaling={false} style={itemDetailsSty.myCartItemPrice}>
                    {'('}₹{productDetails?.price?.toFixed(2)} x{' '}
                    {productDetails?.cart_quantity}
                    {')'}
                  </Text>
                )}

              {/* {StoreType.PREMEAL === storeData?.store_type && (
                <View style={itemDetailsSty.isPaidByCompanyBadge}>
                  <Text allowFontScaling={false} style={itemDetailsSty.isPaidByCompanyText}>
                    {t('PAID_BY_COMPANY')}
                  </Text>
                </View>
              )} */}
            </View>

            {(StoreType.QSR === storeData?.store_type || StoreType.KOT === storeData?.store_type) ? (
              productDetails?.is_in_cart ? (
                <View style={[itemDetailsSty.row, itemDetailsSty.quantityContainer]}>
                  <TouchableOpacity
                    onPress={() =>
                      handleDecrement(productDetails?.cart_id?.toString() ?? '')
                    }
                    style={itemDetailsSty.myCartItemQuantityMinusBtn}
                  >
                    <MINUS_IC width={11} />
                  </TouchableOpacity>
                  <Text allowFontScaling={false} style={itemDetailsSty.myCartItemQuantity}>
                    {productDetails?.cart_quantity}
                  </Text>
                  <TouchableOpacity
                    onPress={() => {
                      handleIncrement(
                        productDetails?.cart_id?.toString() ?? '',
                      );
                    }}
                    style={itemDetailsSty.myCartItemQuantityPlusBtn}
                  >
                    <PLUS_IC height={12} width={12} />
                  </TouchableOpacity>
                </View>
              ) : (
                <TouchableOpacity
                  onPress={() => {
                    handleAddToCart(productDetails?.id?.toString() ?? '');
                  }}
                  disabled={!productDetails?.is_in_stock}
                  style={{
                    flexDirection: 'row',
                    alignItems: 'center',
                    gap: 6,
                    borderWidth: 1,
                    borderColor: _COL.BORDER,
                    paddingHorizontal: 12,
                    paddingVertical: 5,
                    borderRadius: 20,
                    opacity: productDetails?.is_in_stock ? 1 : 0.4,
                  }}
                >
                  <MY_CART_IC />
                  <Text allowFontScaling={false}>{t('ADD_TO_CART')}</Text>
                </TouchableOpacity>
              )
            ) : null}
          </View>

          <View style={itemDetailsSty.itemDetailsContainer}>
            {detailsItems.map((detail: any) => {
              const isExpanded = expandedIds.includes(detail.id);
              return (
                <View key={detail.id}>
                  <TouchableOpacity
                    style={itemDetailsSty.itemDetail}
                    activeOpacity={0.8}
                    onPress={() => toggleExpand(detail.id)}
                  >
                    <View style={itemDetailsSty.thirdRow}>
                      {detail.image}
                      <Text allowFontScaling={false} style={itemDetailsSty.itemDetailText}>{detail.title}</Text>
                    </View>
                    <DOWN_ARROW
                      style={{
                        transform: [{ rotate: isExpanded ? '180deg' : '0deg' }],
                      }}
                    />
                  </TouchableOpacity>

                  {isExpanded && (
                    <ScrollView
                      horizontal={detail.menu ? true : false}
                      scrollEnabled={detail.menu ? true : false}
                      showsHorizontalScrollIndicator={false}
                    >
                      <View style={{ overflow: 'hidden', flexDirection: detail.menu ? 'row' : 'column' }}>
                        {(detail.ingredients
                          ? detail.ingredients
                          : detail.menu
                            ? Object.values(detail.menu)
                            : [detail.content]
                        ).map((item: any, index: number) => (
                          <Animated.View
                            key={index}
                            entering={FadeIn.duration(300)}
                            exiting={FadeOut.duration(300)}
                            layout={Layout.springify()}
                          >
                            {detail.ingredients ? (
                              <View style={itemDetailsSty.thirdRow}>
                                <Text allowFontScaling={false} style={itemDetailsSty.dotText}>•</Text>
                                <Text allowFontScaling={false} style={[itemDetailsSty.text, { textTransform: 'capitalize' }]}>{item}</Text>
                              </View>
                            ) : detail.menu ? (
                              <View style={itemDetailsSty.menuContainer}>
                                <View style={itemDetailsSty.menuHeader}>
                                  <Text allowFontScaling={false} style={itemDetailsSty.menuTitle}>{item.day[0].toUpperCase()}{item.day.slice(1).toLowerCase()}</Text>
                                  {item.day === new Date().toLocaleString('en-US', { weekday: 'long' }).toUpperCase() && (
                                    <Text allowFontScaling={false} style={itemDetailsSty.menuTodayText}>
                                      • {t('TODAY')}
                                    </Text>
                                  )}
                                </View>
                                <View style={itemDetailsSty.menuItems}>
                                  {item.available === false ? (
                                    <View style={itemDetailsSty.noMenuContainer}>
                                      <Image
                                        source={NO_MENU_IMG}
                                        style={itemDetailsSty.noMenuImage}
                                      />
                                      <Text allowFontScaling={false} style={itemDetailsSty.noMenuText}>
                                        {t('NO_MENU_AVAILABLE')}
                                      </Text>
                                    </View>
                                  ) : (
                                    item.menu_items?.map(
                                      (menuItem: { item: string }, idx: number) => (
                                        <View key={idx} style={itemDetailsSty.secondRow}>
                                          <Text allowFontScaling={false} style={itemDetailsSty.menuDotText}>•</Text>
                                          <Text allowFontScaling={false} style={itemDetailsSty.menuText}>
                                            {menuItem.item}
                                          </Text>
                                        </View>
                                      ),
                                    )
                                  )}
                                </View>
                              </View>
                            ) : (
                              <Text allowFontScaling={false} style={itemDetailsSty.text}>{item}</Text>
                            )}
                          </Animated.View>
                        ))}
                      </View>
                    </ScrollView>
                  )}
                </View>
              );
            })}
          </View>
        </View>
      </Animated.ScrollView>

      <Animated.View style={[itemDetailsSty.imageContainer, { height: IMAGE_HEIGHT }, imageAnimatedStyle]}>
        <ScrollView
          ref={scrollRef}
          horizontal
          pagingEnabled
          onScroll={onScroll}
          showsHorizontalScrollIndicator={false}
          scrollEventThrottle={16}
          bounces={false}
          overScrollMode="never"
        >
          {(productDetails?.images ?? [])?.length > 0 ? productDetails?.images?.map((item, index) => (
            <ImageBackground
              key={index}
              source={{ uri: item }}
              resizeMode="cover"
              style={[itemDetailsSty.imageBackground, { height: IMAGE_HEIGHT }]}
            />
          )) : productDetails?.thumbnail ? (
            <ImageBackground
              key={0}
              source={{ uri: productDetails?.thumbnail }}
              resizeMode="cover"
              style={[itemDetailsSty.imageBackground, { height: IMAGE_HEIGHT }]}
            />
          ) : null}
        </ScrollView>

        <View style={itemDetailsSty.dotContainer}>
          {productDetails?.images?.map((_, i) => (
            <View
              key={i}
              style={[itemDetailsSty.dot, page === i && itemDetailsSty.activeDot]}
            />
          ))}
        </View>
      </Animated.View>

      {/* <Animated.View
        style={[
          itemDetailsSty.animatedTitleContainer,
          { top: IMAGE_HEIGHT + 16, left: 16 },
          titleAnimatedStyle,
        ]}
        pointerEvents="none"
      >
        <Text allowFontScaling={false} style={itemDetailsSty.animatedTitle} numberOfLines={1}>
          {productDetails?.name}
        </Text>
      </Animated.View> */}

      <Animated.View
        style={[itemDetailsSty.headerContainer, { paddingTop: top }]}
        pointerEvents="box-none"
      >
        <Animated.View style={[itemDetailsSty.headerBg, headerBgStyle]} />

        <View style={itemDetailsSty.headerContent}>
          <TouchableOpacity
            onPress={() => navigation.goBack()}
            style={itemDetailsSty.headerBtn}
          >
            <BACK_ARROW_IC />
          </TouchableOpacity>

          <View style={itemDetailsSty.headerTitleContainer} />

          {StoreType.PREMEAL !== storeData?.store_type && (
            isFromMyCart ? (
              <View style={itemDetailsSty.headerPlaceholder} />
            ) : (
              <TouchableOpacity
                onPress={() => {
                  navigation.navigate('MyCart');
                }}
                style={itemDetailsSty.headerBtn}
              >
                <MY_CART_IC />
                {!!cartItemCount && cartItemCount > 0 &&
                  <View style={itemDetailsSty.cartItemCountContainer}>
                    <Text allowFontScaling={false} style={itemDetailsSty.cartItemCountText}>{cartItemCount}</Text>
                  </View>
                }
              </TouchableOpacity>
            )
          )}
        </View>
      </Animated.View>

      <LinearGradient
        colors={[_COL.WHITE_0, _COL.WHITE, _COL.WHITE]}
        style={{
          position: 'absolute',
          height: bottom + 120,
          bottom: 0,
          left: 0,
          right: 0,
        }}
        start={{ x: 0, y: 0 }}
        end={{ x: 0, y: 1 }}
        pointerEvents="box-none"
      >
        {StoreType.PREMEAL === storeData?.store_type ? (

          <View style={itemDetailsSty.secondRow}>
            <BTN
              title={t('SCHEDULE_BOOKING')}
              onP={() => { setIsBooking(true); }}
              borderR={120}
              // width={_WIDTH / 2 - 20}
              width={_WIDTH - 32}
              ctr
              mTop={50}
              mLeft={16}
              tCol={_COL.FINAL_BLACK}
              bgCol={_COL.WHITE}
              bordered
              borderW={1}
              borderCol={_COL.FINAL_BLACK}
            />
            {/* <BTN
              title={t('ORDER_NOW')}
              onP={handlePremealOrderNow}
              borderR={120}
              width={_WIDTH / 2 - 20}
              ctr
              mTop={50}
              mLeft={25}
              mRight={16}
              isDisabled={!productDetails?.premeal_info?.today?.available}
            /> */}
          </View>
        ) : (
          <BTN
            title={t('ORDER_NOW')}
            onP={handleOrderNow}
            borderR={120}
            width={_WIDTH - 32}
            ctr
            mTop={50}
            isDisabled={!productDetails?.is_in_stock}
          />
        )}
      </LinearGradient>

      <ScheduleCalendarSheet
        isBooking={isBooking}
        setIsBooking={setIsBooking}
        data={productDetails?.premeal_info}
        onBook={handleScheduleCheck}
        error={error}
      />
      <ConfirmationAlert ref={alertRef} onConfirm={() => { }} t={t} />
    </View>
  );
}
