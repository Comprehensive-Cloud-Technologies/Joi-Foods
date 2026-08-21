import React, { useRef } from 'react';
import Home from '../screens/Home';
import Orders from '../screens/MyOrdersScr';
import Notifications from 'screens/Notifications';
import Profile from 'screens/Profile';
import { _COL, FONT, isIOS, Tab } from 'utils';
import { StyleSheet, TouchableOpacity, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MENU_ICON, NOTIFICATION_ICON, ORDER_ICON, PROFILE_ICON, SCAN_IC } from 'assets';
import { ConfirmationAlert, ConfirmationAlertRefT } from 'components';
import { useT } from 'internationalization';
import { useNavigation } from '@react-navigation/native';

const BottomTabs = () => {
  const navigation = useNavigation<any>();
  const insets = useSafeAreaInsets();
  const alertRef = useRef<ConfirmationAlertRefT>(null);
  const { t } = useT();

  const CustomTabbarButton = ({ children, onPress }: any) => (
    <View style={styles.scanButtonContainer}>
      <TouchableOpacity onPress={onPress} style={styles.scanButton}>
        <View>{children}</View>
      </TouchableOpacity>
    </View>
  );

  return (
    <>
      <Tab.Navigator
        initialRouteName="Menu"
        screenOptions={({ route }) => ({
          unmountOnBlur: true,
          headerShown: false,
          tabBarLabelStyle: {
            fontFamily: FONT.SEMI_BOLD,
            fontSize: 10,
            marginBottom: isIOS ? 5 : 2,
          },
          tabBarActiveTintColor: _COL.MAIN_BLACK,
          tabBarInactiveTintColor: _COL.TEXT_GREY_LIGHT,
          tabBarAllowFontScaling: false,
          tabBarStyle: {
            backgroundColor: _COL.WHITE,
            paddingTop: 5,
            paddingBottom: isIOS ? insets.bottom + 10 : insets.bottom + 10,
            marginBottom: isIOS ? 2 : 0,
            height: isIOS ? 90 : 70 + insets.bottom,
            paddingHorizontal: 5,
            ...styles.shadow,
          },
        })}
      >
        <Tab.Screen
          name="Menu"
          component={Home}
          options={{
            tabBarIcon: ({ focused }) => <MENU_ICON isActive={focused} />,
          }}
        />
        <Tab.Screen
          name="Orders"
          component={Orders}
          options={{
            tabBarIcon: ({ focused }) => <ORDER_ICON isActive={focused} />,
          }}
        />
        <Tab.Screen
          name="Scan QR"
          component={Home}
          options={{
            tabBarLabelStyle: {
              fontFamily: FONT.SEMI_BOLD,
              fontSize: 10,
              position: 'absolute',
              bottom: isIOS ? -35 : -40,
              alignSelf: 'center',
            },
            tabBarIcon: ({ focused }) => <SCAN_IC />,
            tabBarButton: props => (
              <View>
                <CustomTabbarButton
                  {...props}
                  onPress={() => {
                    navigation.navigate('ScanQRCode', { isFromStoreChange: true });
                  }}
                />
              </View>
            ),
          }}
        />
        <Tab.Screen
          name="Notifications"
          component={Notifications}
          options={{
            tabBarIcon: ({ focused }) =>
              <NOTIFICATION_ICON isActive={focused} />,
          }}
        />
        <Tab.Screen
          name="Profile"
          component={Profile}
          options={{
            tabBarIcon: ({ focused }) =>
              <PROFILE_ICON isActive={focused} />,
          }}
        />
      </Tab.Navigator>
      <ConfirmationAlert ref={alertRef} onConfirm={() => { }} t={t} />
    </>
  );
};

export default BottomTabs;

const styles = StyleSheet.create({
  shadow: {
    shadowColor: _COL.SHADOW_COLOR,
    shadowOffset: { width: 0, height: -10 },
    shadowOpacity: 0.25,
    shadowRadius: 14,
    elevation: 10,
  },
  scanButtonContainer: {
    justifyContent: 'center',
    alignItems: 'center',
    width: 70,
    height: 70,
    borderRadius: 35,
    backgroundColor: _COL.WHITE,
    top: -38,
  },
  scanButton: {
    width: 57,
    height: 57,
    borderRadius: 30,
    backgroundColor: _COL.PRIMARY_RED,
    justifyContent: 'center',
    alignItems: 'center',
  },
  scanButtonText: {
    color: _COL.TEXT_GREY_LIGHT,
    fontFamily: FONT.SEMI_BOLD,
    position: 'absolute',
    fontSize: 10,
    bottom: '-205%',
  },
});
