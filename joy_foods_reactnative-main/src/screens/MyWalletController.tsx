import { GETreq, POSTreq } from 'api';
import { BACK_BTN_IC, CREDIT_DEBIT_IC, HISTORY_IMG } from 'assets';
import { RechargeWalletSheet, useLoader, useSnackbar } from 'components';
import { useT } from 'internationalization';
import React, { useContext, useEffect, useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, FlatList, ImageBackground, Pressable, ActivityIndicator, RefreshControl } from 'react-native';
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';
import { StackProps, TransactionListT, TransactionT } from 'types';
import { _COL, FONT, isIOS } from 'utils';
import RazorpayCheckout, { SuccessResponse } from 'react-native-razorpay';
import { AppCtx } from 'store';
import { SET_PROFILE_DATA } from 'store/context';

const MyWalletController = ({ navigation }: StackProps<'MyWalletScr'>) => {
  const { t } = useT();
  const [transactionsData, setTransactionsData] = useState<TransactionT>();
  const [transactions, setTransactions] = useState<TransactionListT[]>([]);
  const [page, setPage] = useState(1);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const [hasMore, setHasMore] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const { showLoader, hideLoader } = useLoader();
  const [isRechargeVisible, setIsRechargeVisible] = useState(false);
  const { showSnackbar } = useSnackbar();
  const {
    dispatch,
    state: { profileData },
  } = useContext(AppCtx);
  const { top } = useSafeAreaInsets();

  const getTransactionHistory = async (
    targetPage: number = 1,
    isRefresh: boolean = false,
  ) => {
    try {
      if (isRefresh) {
        setIsRefreshing(true);
      } else if (targetPage === 1) {
        showLoader();
      } else {
        setIsLoadingMore(true);
      }

      const payload = {
        page: targetPage,
        per_page: 10,
      };
      const { data, success } = await GETreq('profile/wallet', payload);
      console.log('Wallet Data::', JSON.stringify(data, null, 3));

      if (success && data?.data) {
        dispatch({
          type: SET_PROFILE_DATA,
          profileData: {
            ...profileData,
            wallet: {
              ...profileData?.wallet,
              available_balance: data?.data?.wallet?.available_balance,
              formatted_balance: data?.data?.wallet?.formatted_balance,
            },
          },
        });
        setTransactionsData(data.data);
        const newTransactions = data.data.transactions || [];

        if (targetPage === 1) {
          setTransactions(newTransactions);
        } else {
          setTransactions(prev => [...prev, ...newTransactions]);
        }

        const pagination = data.data.pagination;
        setHasMore(pagination?.has_next || false);
        setPage(targetPage);
      }
    } catch (error) {
      console.log(error);
    } finally {
      hideLoader();
      setIsLoadingMore(false);
      setIsRefreshing(false);
    }
  };

  const handleLoadMore = () => {
    if (!isLoadingMore && hasMore) {
      getTransactionHistory(page + 1);
    }
  };

  useEffect(() => {
    getTransactionHistory();
  }, []);

  const completeRecharge = async (razorpayRes?: SuccessResponse) => {
    try {
      const payload: any = {};
      if (razorpayRes?.razorpay_order_id) {
        payload.razorpay_order_id = razorpayRes.razorpay_order_id;
      }
      if (razorpayRes?.razorpay_payment_id) {
        payload.razorpay_payment_id = razorpayRes.razorpay_payment_id;
      }
      if (razorpayRes?.razorpay_signature) {
        payload.razorpay_signature = razorpayRes.razorpay_signature;
      }
      const { success, data } = await POSTreq(
        'profile/recharge_complete',
        payload,
        true,
      );
      console.log('Wallet Complete Data::', JSON.stringify(data, null, 3));

      if (success) {
        hideLoader();
        dispatch({
          type: SET_PROFILE_DATA,
          profileData: {
            ...profileData,
            wallet: {
              ...profileData?.wallet,
              available_balance: data?.data?.wallet?.new_balance,
              formatted_balance: data?.data?.wallet?.formatted_balance,
            },
          },
        });

        showSnackbar(data?.message, 'success');
        getTransactionHistory(1, true);
      }
    } catch (error) {
      console.log(error);
    }
  };

  const handleRecharge = async (amount: string) => {
    setIsRechargeVisible(false);
    try {
      showLoader();
      const { success, data } = await POSTreq(
        'profile/recharge_initiate',
        { amount },
        true,
      );
      console.log('Wallet Initiate Data::', JSON.stringify(data, null, 3));

      if (success) {
        hideLoader();
        const options = {
          description: data?.data?.razorpay?.description,
          image: data?.data?.razorpay?.image,
          currency: data?.data?.razorpay?.currency,
          key: data?.data?.razorpay?.key,
          amount: data?.data?.razorpay?.amount,
          name: data?.data?.razorpay?.name,
          order_id: data?.data?.razorpay?.order_id,
          prefill: {
            email: data?.data?.razorpay?.prefill?.email,
            contact: data?.data?.razorpay?.prefill?.contact,
            name: data?.data?.razorpay?.prefill?.name,
          },
          theme: { color: data?.data?.razorpay?.theme?.color },
        };
        console.log('Options::', JSON.stringify(options, null, 3));
        RazorpayCheckout.open(options)
          .then(DATA => {
            console.log('Success:', DATA);
            completeRecharge(DATA);
          })
          .catch(error => {
            showSnackbar(error?.code == 0 ? "Payment processing cancelled by user" : error?.description, 'error');
            console.log('Error:', error);
          });
      } else {
        hideLoader();
        showSnackbar(data?.message, 'error');
      }
    } catch (error) {
      console.log(error);
    } finally {
      hideLoader();
    }
  };

  const renderTransaction = (transaction: TransactionListT, index: number) => {
    const isCredit = transaction.type === 'CREDIT';
    const amountColor = isCredit ? _COL.SECONDARY_ORANGE : _COL.PRIMARY_RED;

    return (
      <View
        key={transaction.id}
        style={[
          styles.transactionItem,
          { borderBottomWidth: index === transactions.length - 1 ? 0 : 1 },
        ]}
      >
        <View
          style={{ backgroundColor: '#F4F4F4', padding: 8, borderRadius: 28 }}
        >
          <CREDIT_DEBIT_IC
            color={isCredit ? _COL.SECONDARY_ORANGE : _COL.PRIMARY_RED}
            style={{ transform: [{ rotate: isCredit ? '180deg' : '0deg' }] }}
          />
        </View>

        <View style={styles.transactionDetails}>
          <Text allowFontScaling={false} style={styles.transactionTitle}>{transaction.label}</Text>
          <Text allowFontScaling={false} style={styles.transactionDate}>
            {transaction.formatted_date} {transaction.time}
          </Text>
        </View>

        <Text allowFontScaling={false} style={[styles.transactionAmount, { color: amountColor }]}>
          {transaction.formatted_amount}
        </Text>
      </View>
    );
  };

  return (
    <>
      <SafeAreaView style={styles.container}>
        <View style={{ flexDirection: 'row', }}>
          <TouchableOpacity
            style={styles.backBtn}
            onPress={() => {
              navigation.goBack();
            }}
          >
            <BACK_BTN_IC />
          </TouchableOpacity>

          <Text allowFontScaling={false} style={styles.title}>{t('MY_WALLET')}</Text>
        </View>
        <View style={styles.divider} />

        <ImageBackground source={HISTORY_IMG} style={styles.balanceCard}>
          <View>
            <Text allowFontScaling={false} style={styles.balanceLabel}>{t('WALLET_BALANCE')}</Text>
            <Text allowFontScaling={false} style={styles.balanceAmount}>
              ₹{transactionsData?.wallet?.available_balance?.toFixed(2)}
            </Text>
          </View>
          <Pressable
            style={{
              borderWidth: 1,
              borderColor: _COL.WHITE,
              paddingHorizontal: 18,
              paddingVertical: 10,
              borderRadius: 52,
            }}
            onPress={() => setIsRechargeVisible(true)}
          >
            <Text allowFontScaling={false} style={[styles.balanceLabel, { fontFamily: FONT.SEMI_BOLD }]}>
              {t('RECHARGE')}
            </Text>
          </Pressable>
        </ImageBackground>

        <View style={styles.historySection}>
          <Text allowFontScaling={false} style={styles.historyTitle}>{t('TRANSACTION_HISTORY')}</Text>
        </View>

        <FlatList
          data={transactions}
          renderItem={({ item, index }) => renderTransaction(item, index)}
          keyExtractor={item =>
            (item.id || item.uuid || Math.random()).toString()
          }
          showsVerticalScrollIndicator={false}
          contentContainerStyle={styles.transactionList}
          onEndReached={handleLoadMore}
          onEndReachedThreshold={0.5}
          style={styles.transactionListScroll}
          refreshControl={
            <RefreshControl
              refreshing={isRefreshing}
              onRefresh={() => {
                getTransactionHistory(1, true);
              }}
            />
          }
          ListFooterComponent={
            isLoadingMore ? (
              <ActivityIndicator
                size="small"
                color={_COL.PRIMARY_RED}
                style={{ marginVertical: 20 }}
              />
            ) : (
              <View style={{ height: 0 }} />
            )
          }
        />
      </SafeAreaView>
      <RechargeWalletSheet
        isVisible={isRechargeVisible}
        setIsVisible={() => setIsRechargeVisible(false)}
        onProceed={handleRecharge}
      />
    </>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff',
  },
  backBtn: {
    position: 'absolute',
    left: 16,
    top: 12,
    zIndex: 10,
  },
  title: {
    fontFamily: FONT.SEMI_BOLD,
    fontSize: 20,
    textAlign: 'center',
    flex: 1,
    color: _COL.FINAL_BLACK,
    paddingTop: 13
  },
  divider: {
    height: 1,
    backgroundColor: _COL.BORDER_FOURTH,
    marginTop: isIOS ? 13 : 10,
  },
  scrollView: {
    flex: 1,
  },
  balanceCard: {
    marginHorizontal: 16,
    marginTop: 16,
    borderRadius: 12,
    paddingTop: 22,
    paddingBottom: 19,
    overflow: 'hidden',
    elevation: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
  },
  balanceLabel: {
    fontSize: 14,
    color: _COL.WHITE,
    fontFamily: FONT.MEDIUM,
    textAlign: 'center',
  },
  balanceAmount: {
    fontSize: 26,
    fontFamily: FONT.BOLD,
    color: _COL.WHITE,
    textAlign: 'center',
    marginTop: 6,
  },
  historySection: {
    marginTop: 18,
    paddingHorizontal: 16,
  },
  historyTitle: {
    fontSize: 16,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.FINAL_BLACK,
  },
  listContainer: {
    paddingHorizontal: 16,
  },
  transactionListScroll: {
    flex: 1,
    marginTop: 12,
    marginHorizontal: 16,
    marginBottom: 20,
  },
  transactionList: {
    borderWidth: 1,
    borderRadius: 12,
    borderColor: _COL.BORDER_FIFTH,
    paddingHorizontal: 16,
    backgroundColor: _COL.WHITE,
  },
  transactionItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 16,
    borderBottomWidth: 1,
    borderBottomColor: _COL.BORDER_FOURTH,
  },
  transactionDetails: {
    flex: 1,
    marginHorizontal: 12,
  },
  transactionTitle: {
    fontSize: 14,
    fontFamily: FONT.MEDIUM,
    color: _COL.FINAL_BLACK,
    marginBottom: 4,
  },
  transactionDate: {
    fontSize: 12,
    fontFamily: FONT.REGULAR,
    color: _COL.TEXT_GREY_LIGHT,
  },
  transactionAmount: {
    fontSize: 14,
    fontFamily: FONT.SEMI_BOLD,
  },
});

export default MyWalletController;
