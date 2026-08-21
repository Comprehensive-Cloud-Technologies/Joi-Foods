import { TransitionPresets } from '@react-navigation/stack';
import { _COL, AppStack, isIOS } from 'utils';
import CTX, { SET_MISC_DATA, RESET_APP_STATE } from 'store/context';
import { useContext, useEffect, useRef, useState } from 'react';
import { View, StyleSheet, AppState, DeviceEventEmitter } from 'react-native';
import { OnboardingScreen, SplashScr, CompanyCode, LoginScreen, ForgotPasswordScreen, VerifyOtpScreen, SetNewPasswordScreen, CreateAccount, SelectStore, Search, CategoryList, CategoryWiseProductList, MyCart, PopularItemsList, ItemDetails, OrderSummary, CMSScr, OrderSuccessfullScr, OrderDetailsScr, EditProfileScr, SettingsScr, ChangePasswordScr, MyWalletScr, SupportScr, SupportSuccessScr, SupportContactScr, NoInternetScr, ScanQRController } from 'screens';
import { accessTokenAtom, companyCodeAtom, isonboardingAtom, storeDataAtom, useResetAtoms } from 'store/atoms';
import { useAtom } from 'jotai';
import BottomTabs from 'navigations/BottomTabs';
import { GETreq } from 'api';
import { useNetInfo } from '@react-native-community/netinfo';
import { Linking } from 'react-native';
import useValidateStoreHook from 'hooks/useValidateStoreHook';
import { useNavigation } from '@react-navigation/native';
import { useNotificationHandler } from 'notifications/notificationService';
import { SessionExpiredModal } from 'components';
import DeleteConfirmationModal from 'components/DeleteModel';
import DeviceInfo from 'react-native-device-info';

const { Navigator, Screen, Group } = AppStack;

function AppStackNav() {
  const { dispatch, state: { isSplashHide } } = useContext(CTX);

  const { isConnected } = useNetInfo();
  const [isonboarding] = useAtom(isonboardingAtom);
  const [companyCodeData] = useAtom(companyCodeAtom);
  const [accessToken] = useAtom(accessTokenAtom);
  const [storeData] = useAtom(storeDataAtom);
  const [isSessionExpired, setIsSessionExpired] = useState(false);
  const [showUpdateModel, setShowUpdateModel] = useState(false);
  const [isForceUpdate, setIsForceUpdate] = useState(false);
  const [storeUrl, setStoreUrl] = useState<string>("");
  const resetAtoms = useResetAtoms();

  function isNewerVersion(remote: string, current: string, remote_code: string, current_code: string): boolean {
    const remoteParts = remote.split(".").map(Number);
    const currentParts = current.split(".").map(Number);
    const remoteCodeParts = remote_code.split(".").map(Number);
    const currentCodeParts = current_code.split(".").map(Number);
    const length = Math.max(remoteParts.length, currentParts.length, remoteCodeParts.length, currentCodeParts.length);

    for (let i = 0; i < length; i++) {
      const r = remoteParts[i] ?? 0;
      const c = currentParts[i] ?? 0;
      const rc = remoteCodeParts[i] ?? 0;
      const cc = currentCodeParts[i] ?? 0;
      if (r > c) return true;
      if (r < c) return false;
      if (rc > cc) return true;
      if (rc < cc) return false;
    }
    return false;
  }

  const getMiscData = async () => {
    try {
      const { success, data } = await GETreq('misc/config');
      console.log("MiscData::", success, JSON.stringify(data, null, 3));
      if (success) {
        dispatch({ type: SET_MISC_DATA, miscData: data?.data });
        const remote = isIOS ? data?.data?.ios_version_name : data?.data?.android_version_name;
        const current = DeviceInfo.getVersion();
        const remote_code = (isIOS ? data?.data?.ios_version_code : data?.data?.android_version_code)?.toString();
        const current_code = DeviceInfo.getBuildNumber().toString();

        if (isNewerVersion(remote, current, remote_code, current_code)) {
          setShowUpdateModel(true);
          setIsForceUpdate(isIOS ? data?.data?.force_ios_update : data?.data?.force_android_update);
          setStoreUrl(isIOS ? data?.data?.ios_version_url : data?.data?.android_version_url);
        }
      }
    } catch (error) {
      console.log(error);
    }
  };

  const { validateStore } = useValidateStoreHook();
  const navigation = useNavigation<any>();

  const isInitialUrlHandled = useRef(false);

  useNotificationHandler();

  useEffect(() => {
    getMiscData();
  }, []);

  useEffect(() => {
    const handleVerifySession = async () => {
      if (accessToken && accessToken.length > 0) {
        try {
          const { success, data } = await GETreq('auth/verify_session');
          console.log("Verify Session::", success, JSON.stringify(data, null, 3));
        } catch (e) {
          console.log(e);
        }
      }
    };

    // Call on mount (kill mode)
    handleVerifySession();

    // Call on foreground transition
    const appStateSubscription = AppState.addEventListener('change', nextAppState => {
      if (nextAppState === 'active') {
        handleVerifySession();
      }
    });

    const sessionExpiredSub = DeviceEventEmitter.addListener('session_expired', () => {
      if (accessToken && accessToken.length > 0) {
        setIsSessionExpired(true);
      }
    });

    return () => {
      appStateSubscription.remove();
      sessionExpiredSub.remove();
    };
  }, [accessToken]);

  useEffect(() => {
    const handleDeepLink = async (url: string | null) => {
      if (!url || accessToken.length === 0) {
        return;
      }

      console.log('AppStack: Deep Link Received:', url);
      const { success } = await validateStore(url, { silent: false });
      if (success) {
        // navigation.reset({
        //   index: 0,
        //   routes: [{ name: 'AppNavigator', params: { screen: 'Menu' } }],
        // });
        navigation.reset({
          index: 0,
          routes: [
            {
              name: 'AppNavigator',
              state: {
                routes: [{ name: 'Menu' }],
              },
            }
          ],
        });
      }
    };

    if (!isInitialUrlHandled.current && accessToken.length !== 0) {
      Linking.getInitialURL().then(url => {
        if (url) {
          isInitialUrlHandled.current = true;
          handleDeepLink(url);
        }
      });
    }

    const subscription = Linking.addEventListener('url', ({ url }) => {
      handleDeepLink(url);
    });

    return () => {
      subscription.remove();
    };
  }, [validateStore, accessToken, navigation]);

  return (
    <View style={styles.root}>
      <Navigator
        screenOptions={{
          headerShown: false,
          headerStyle: { height: 0, display: 'none' },
          ...TransitionPresets.SlideFromRightIOS,
        }}
      >
        {!isSplashHide && <Screen name="SplashScr" component={SplashScr} />}
        {!isonboarding && (
          <Screen name="OnboardingScreen" component={OnboardingScreen} />
        )}
        {accessToken.length !== 0 ? (
          storeData.store_code.length !== 0 ? (
            <Group>
              <Screen name="AppNavigator" component={BottomTabs} />
              <Screen name="SupportScr" component={SupportScr} />
              <Screen name="SupportContactScr" component={SupportContactScr} />
              <Screen name="ChangePasswordScr" component={ChangePasswordScr} />
              <Screen name="OrderDetailsScr" component={OrderDetailsScr} />
              <Screen name="SettingsScr" component={SettingsScr} />
              <Screen name="OrderSuccessfull" component={OrderSuccessfullScr} />
              <Screen name="OrderSummary" component={OrderSummary} />
              <Screen name="ItemDetails" component={ItemDetails} />
              <Screen name="PopularItemsList" component={PopularItemsList} />
              <Screen name="MyCart" component={MyCart} />
              <Screen name="EditProfileScr" component={EditProfileScr} />
              <Screen name="MyWalletScr" component={MyWalletScr} />
              <Screen
                name="CategoryWiseProductList"
                component={CategoryWiseProductList}
              />
              <Screen name="CategoryList" component={CategoryList} />
              <Screen name="Search" component={Search} />
              <Screen name="SupportSuccessScr" component={SupportSuccessScr} />
              <Screen
                name="SelectStoreScr"
                component={SelectStore}
                options={{ ...TransitionPresets.ModalSlideFromBottomIOS }}
              />
              <Screen name="ScanQRCode" component={ScanQRController} />
            </Group>
          ) : (
            <Group>
              <Screen name="SelectStore" component={SelectStore} />
              <Screen name="ScanQR" component={ScanQRController} />
            </Group>
          )
        ) : (
          <Group>
            {companyCodeData?.company_code == '' ? (
              <Screen name="CompanyCode" component={CompanyCode} />
            ) : (
              <Group>
                <Screen name="LoginScreen" component={LoginScreen} />
                <Screen name="CreateAccount" component={CreateAccount} />
                <Screen
                  name="ForgotPasswordScreen"
                  component={ForgotPasswordScreen}
                />
                <Screen name="VerifyOtpScreen" component={VerifyOtpScreen} />
                <Screen
                  name="SetNewPasswordScreen"
                  component={SetNewPasswordScreen}
                />
              </Group>
            )}
          </Group>
        )}
        <Screen name="CMSController" component={CMSScr} />
      </Navigator>

      {isConnected === false && (
        <View style={styles.overlay}>
          <NoInternetScr />
        </View>
      )}

      <SessionExpiredModal
        visible={isSessionExpired}
        title={'Session Expired'}
        message={'Your session has expired. Please login again.'}
        confirmText={'Okay'}
        onClose={() => { }}
        onConfirm={() => {
          setIsSessionExpired(false);
          dispatch({ type: RESET_APP_STATE });
          resetAtoms();
        }}
      />
      <DeleteConfirmationModal
        visible={showUpdateModel}
        title={'Update Available'}
        message={'Update the app to continue enjoying the best experience.'}
        cancelText={'Later'}
        confirmText={'Update Now'}
        onClose={() => {
          setShowUpdateModel(false);
        }}
        onConfirm={() => {
          if (storeUrl && storeUrl.length > 0) {
            Linking.openURL(storeUrl);
          }
        }}
        cancelTextCol={_COL.PRIMARY_RED}
        disableClose={isForceUpdate}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1 },
  overlay: {
    ...StyleSheet.absoluteFillObject,
    zIndex: 9999,
  },
});

export default AppStackNav;
