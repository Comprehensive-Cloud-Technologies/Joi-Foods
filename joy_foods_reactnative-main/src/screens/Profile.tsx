import { GETreq } from 'api';
import {
  ABOUT_US_IC,
  EDIT_PROFILE_IC,
  LOGOUT_IC,
  SETTINGS_IC,
  TELEPHONE_IC,
  WALLET_IMG,
} from 'assets';
import { SettingsMenuItem, useLoader, useSnackbar } from 'components';
import DeleteConfirmationModal from 'components/DeleteModel';
import { useT } from 'internationalization';
import React, { useContext, useEffect, useState } from 'react';
import {
  View,
  Text,
  Image,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  ImageBackground,
  Pressable,
} from 'react-native';
import LinearGradient from 'react-native-linear-gradient';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { AppCtx } from 'store';
import { useResetAtoms } from 'store/atoms';
import { RESET_APP_STATE, SET_PROFILE_DATA } from 'store/context';
import { NavProps } from 'types';
import { _COL, _H, FONT, isIOS } from 'utils';

const Profile = ({ navigation }: NavProps<'Profile'>) => {
  const { top, bottom } = useSafeAreaInsets();
  const { t } = useT();
  const { showLoader, hideLoader } = useLoader();
  const [isLogout, setIsLogout] = useState(false);
  const resetAtoms = useResetAtoms();
  const { showSnackbar } = useSnackbar();

  const {
    dispatch,
    state: { profileData, miscData },
  } = useContext(AppCtx);

  const getProfileData = async () => {
    try {
      showLoader();
      const { success, data } = await GETreq('profile/my_profile');
      console.log('Profile data::', JSON.stringify(data, null, 3));
      if (success) {
        dispatch({ type: SET_PROFILE_DATA, profileData: data?.data?.profile });
      }
    } catch (error) {
      console.log(error);
    } finally {
      hideLoader();
    }
  };

  useEffect(() => {
    getProfileData();
  }, []);

  const logoutUser = async () => {
    try {
      showLoader();
      const { data } = await GETreq('auth/logout');
      console.log('Logout data::', JSON.stringify(data, null, 3));
      dispatch({ type: RESET_APP_STATE });
      resetAtoms();
      showSnackbar(data.message);
    } catch (err) {
      console.log(err);
    } finally {
      hideLoader();
    }
  };

  return (
    <View style={styles.container}>
      <LinearGradient
        colors={['#FFF0F1', '#FCFEFF', '#FCFEFF']}
        start={{ x: 0, y: 0 }}
        end={{ x: 0, y: 1 }}
      >
        <View style={[styles.header, { paddingTop: top }]}>
          <Text allowFontScaling={false} style={[styles.headerTitle, { paddingTop: isIOS ? 0 : _H * 0.015 }]}>Profile</Text>
          <TouchableOpacity activeOpacity={0.8} style={styles.logoutButton} onPress={() => { setIsLogout(true) }}>
            <LOGOUT_IC />
          </TouchableOpacity>
        </View>
        <ScrollView
          showsVerticalScrollIndicator={false}
          style={{ height: '100%' }}
        >
          <View style={styles.profileSection}>
            {profileData?.profile_picture ? (
              <Image
                source={{ uri: profileData?.profile_picture }}
                style={styles.profileImage}
              />
            ) : (
              <View
                style={[
                  styles.profileImage,
                  {
                    alignItems: 'center',
                    justifyContent: 'center',
                    backgroundColor: _COL.PRIMARY_RED,
                  },
                ]}
              >
                <Text allowFontScaling={false}
                  style={{
                    fontFamily: FONT.MEDIUM,
                    fontSize: 54,
                    textTransform: 'capitalize',
                    color: _COL.WHITE,
                    lineHeight: isIOS ? undefined : 54,
                  }}
                >
                  {profileData?.full_name?.slice(0, 1)}
                </Text>
              </View>
            )}
            <Text allowFontScaling={false} style={styles.userName}>{profileData?.full_name}</Text>
            <Text allowFontScaling={false} style={styles.userEmail}>{profileData?.email}</Text>
          </View>

          <Pressable
            onPress={() => {
              navigation.navigate('MyWalletScr');
            }}
          >
            <ImageBackground source={WALLET_IMG} style={styles.walletCard}>
              <Text allowFontScaling={false} style={styles.walletLabel}>{t('WALLET_BALANCE')}</Text>
              <Text allowFontScaling={false} style={styles.walletAmount}>
                {profileData?.wallet?.formatted_balance}
              </Text>
            </ImageBackground>
          </Pressable>

          <View style={styles.menuContainer}>
            <SettingsMenuItem
              icon={<EDIT_PROFILE_IC />}
              title={t('EDIT_PROFILE')}
              onPress={() => {
                navigation.navigate('EditProfileScr');
              }}
            />
            <SettingsMenuItem
              icon={<SETTINGS_IC />}
              title={t('SETTINGS')}
              onPress={() => {
                navigation.navigate('SettingsScr');
              }}
            />
            <SettingsMenuItem
              icon={<ABOUT_US_IC />}
              title={t('ABOUT_US')}
              onPress={() => { navigation.navigate('CMSController', { title: 'About Us', content: miscData?.about_us ?? "" }) }}
            />
            <SettingsMenuItem
              icon={<TELEPHONE_IC />}
              title={t('SUPPORT')}
              onPress={() => { navigation.navigate('SupportContactScr') }}
            />
          </View>

          {/* Footer Links */}
          <View style={[styles.footer, { paddingBottom: bottom + 117 }]}>
            <TouchableOpacity onPress={() => { navigation.navigate('CMSController', { title: 'Terms & Conditions', content: miscData?.terms_and_conditions ?? "" }) }}>
              <Text allowFontScaling={false} style={styles.footerLink}>{t('TERMS_OF_USE')}</Text>
            </TouchableOpacity>
            <Text allowFontScaling={false} style={styles.footerSeparator}>|</Text>
            <TouchableOpacity onPress={() => { navigation.navigate('CMSController', { title: 'Privacy Policy', content: miscData?.privacy_policy ?? "" }) }}>
              <Text allowFontScaling={false} style={styles.footerLink}>{t('PRIVACY_POLICY')}</Text>
            </TouchableOpacity>
          </View>
        </ScrollView>
      </LinearGradient>
      <DeleteConfirmationModal
        visible={isLogout}
        title={t('LOGOUT')}
        message={t('LOGOUT_MSG')}
        cancelText={t('NO')}
        confirmText={t('YES')}
        onClose={() => {
          setIsLogout(false);
        }}
        onConfirm={logoutUser}
        cancelTextCol={_COL.PRIMARY_RED}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: 10,
  },
  headerTitle: {
    fontSize: 28,
    color: _COL.FINAL_BLACK,
    fontFamily: FONT.BOLD,
  },
  logoutButton: {
    backgroundColor: _COL.WHITE,
    borderRadius: 50,
    padding: 8,
    borderWidth: 1,
    borderColor: _COL.BORDER_THIRD,
  },
  profileSection: {
    alignItems: 'center',
    paddingTop: 24,
  },
  profileImage: {
    width: 102,
    height: 102,
    borderRadius: 51,
  },
  userName: {
    fontSize: 20,
    color: _COL.FINAL_BLACK,
    fontFamily: FONT.SEMI_BOLD,
    lineHeight: 22,
    marginTop: 20,
    textTransform: 'capitalize',
    width: '100%',
    textAlign: 'center',
  },
  userEmail: {
    fontSize: 14,
    color: _COL.TEXT_GREY_LIGHT,
    fontFamily: FONT.MEDIUM,
    marginTop: 8,
  },
  walletCard: {
    marginTop: 20,
    marginHorizontal: 16,
    borderRadius: 12,
    padding: 24,
    alignItems: 'center',
    overflow: 'hidden',
    paddingBottom: 14
  },
  walletLabel: {
    fontSize: 14,
    color: _COL.FINAL_BLACK,
    fontFamily: FONT.REGULAR,
    lineHeight: 20,
  },
  walletAmount: {
    fontSize: 24,
    color: _COL.THIRD_RED,
    fontFamily: FONT.SEMI_BOLD,
    marginTop: 8,
  },
  menuContainer: {
    paddingHorizontal: 20,
    marginTop: 8,
  },
  footer: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    marginVertical: 30,
  },
  footerLink: {
    fontSize: 14,
    color: _COL.TEXT_GREY_LIGHT,
    fontFamily: FONT.MEDIUM,
    lineHeight: 20,
    textDecorationLine: 'underline',
    paddingTop: isIOS ? 0 : 2
  },
  footerSeparator: {
    fontSize: 14,
    color: _COL.TEXT_GREY_LIGHT,
    marginHorizontal: 8,
  },
});

export default Profile;
