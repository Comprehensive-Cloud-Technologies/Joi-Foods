import { View, Text, StyleSheet, Image, FlatList, RefreshControl, ActivityIndicator, TouchableOpacity } from 'react-native'
import React, { useEffect, useState } from 'react'
import { NavProps, NotificationsItem } from 'types'
import { _COL, _H, FONT } from 'utils'
import { useT } from 'internationalization'
import { useSafeAreaInsets } from 'react-native-safe-area-context'
import { BELL_IC, NO_NOTFICATION_IMG } from 'assets'
import { GETreq } from 'api'
import { useLoader } from 'components/ActivityLoader'

const Notifications = ({ navigation }: NavProps<"Notifications">) => {

  const { t } = useT();
  const { top } = useSafeAreaInsets();
  const [hasNext, setHasNext] = useState(true);
  const [page, setPage] = useState(1);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [bottomLoading, setBottomLoading] = useState(false);
  const [notificationData, setnotificationData] = useState<NotificationsItem[]>([]);
  const { showLoader, hideLoader } = useLoader();

  const getNotificationData = async (isRefresh = false) => {
    try {
      if (notificationData.length === 0) showLoader();
      if (!isRefresh && !hasNext) return;
      if (bottomLoading) return;
      if (isRefresh) {
        setPage(1);
        setIsRefreshing(true);
      } else {
        setBottomLoading(true);
      }
      const payload = {
        page: isRefresh ? 1 : page,
      };
      const { data, success } = await GETreq('notifications/list', payload);
      console.log('Notification DATA::', JSON.stringify(data, null, 3));
      if (success) {
        if (isRefresh) {
          setnotificationData(data?.data?.notifications || []);
        } else {
          setnotificationData((prev) => [...(prev || []), ...(data?.data?.notifications || [])]);
        }
      }
      const pagination = data.data.pagination;
      setHasNext(pagination?.has_next || false);
      setPage(pagination?.current_page + 1);
      setIsRefreshing(false);
      setBottomLoading(false);
      hideLoader();
    }
    catch (e) {
      console.log(e);
      setIsRefreshing(false);
      setBottomLoading(false);
      hideLoader();
    }
  };

  useEffect(() => {
    getNotificationData(true);
  }, []);

  const handleEndReached = () => {
    if (hasNext) {
      getNotificationData();
    }
  };

  const renderNotificationItem = ({ item, index }: { item: NotificationsItem, index: number }) => (
    <TouchableOpacity
      onPress={() => navigation.navigate('OrderDetailsScr', { orderId: item.order_id?.toString() || '' })}
      style={[styles.notificationItem, { paddingTop: index === 0 ? 0 : 12 }]}>
      <View style={styles.bellIcon} >
        <BELL_IC />
      </View>
      <View style={styles.notificationTextContainer}>
        <Text allowFontScaling={false} style={styles.notificationTitle}>{item.message}</Text>
        <Text allowFontScaling={false} style={styles.notificationDateTime}>{item.time_ago}</Text>
      </View>
    </TouchableOpacity>
  );

  return (
    <View style={styles.container}>
      <Text allowFontScaling={false} style={[styles.heading, { marginTop: top + 13 }]}>{t('NOTIFICATION')}</Text>

      {notificationData?.length === 0 ? (
        <View style={styles.emptyContainer}>
          <Image
            source={NO_NOTFICATION_IMG}
            style={styles.noNotificationImg}
            resizeMode='contain'
          />
          <Text allowFontScaling={false} style={styles.noNotificationsMsg}>{t('NO_NOTIFICATIONS_MSG')}</Text>
        </View>
      ) : (
        <FlatList
          data={notificationData}
          keyExtractor={(item, index) => `${item.id}-${index}`}
          renderItem={renderNotificationItem}
          contentContainerStyle={styles.notificationContainer}
          onEndReached={handleEndReached}
          onEndReachedThreshold={0.5}
          refreshControl={
            <RefreshControl
              refreshing={isRefreshing}
              onRefresh={() => getNotificationData(true)}
            />
          }
          ListFooterComponent={
            bottomLoading ? (
              <ActivityIndicator size="large" color={_COL.MAIN_BLACK} />
            ) : null
          }
          showsVerticalScrollIndicator={false}
        />
      )}
    </View>
  )
}
export default Notifications

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.WHITE
  },
  heading: {
    fontSize: 28,
    fontFamily: FONT.BOLD,
    marginLeft: 16,
  },
  noNotificationImg: {
    resizeMode: 'contain',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  noNotificationsMsg: {
    marginTop: 15,
    fontSize: 14,
    color: _COL.MAIN_BLACK,
    fontFamily: FONT.REGULAR,
    textAlign: 'center',
    marginHorizontal: 80,
  },
  notificationItem: {
    flexDirection: 'row',
    borderBottomWidth: 1,
    borderBottomColor: _COL.BORDER,
    paddingVertical: 12,
    alignItems: 'flex-start',
  },
  bellIcon: {
    backgroundColor: _COL.TEXT_GREY_10,
    borderRadius: 20,
    padding: 10,
    marginTop: 3
  },
  notificationTextContainer: {
    marginLeft: 12,
    marginRight: 10,
  },
  notificationTitle: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    color: _COL.BLACK,
    marginRight: 16,
    lineHeight: 20
  },
  notificationDateTime: {
    fontSize: 12,
    fontFamily: FONT.REGULAR,
    color: _COL.TEXT_GREY_LIGHT,
    lineHeight: 18,
    marginTop: 5,
  },
  notificationContainer: {
    paddingHorizontal: 16,
    marginTop: 9,
    paddingBottom: _H * .1
  },
  emptyHeading: {
    fontSize: 18,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.FINAL_BLACK,
    textAlign: 'center',
  }
})



