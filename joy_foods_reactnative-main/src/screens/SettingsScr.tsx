import React, { useContext, useState } from 'react';
import { StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { _COL, FONT, isIOS } from 'utils';
import { BACK_BTN_IC, LOCK_IC, LOGOUT_ICON, DUSTBIN_IC } from 'assets';
import { useT } from 'internationalization';
import DeleteConfirmationModal from 'components/DeleteModel';
import { SettingsMenuItem, useLoader, useSnackbar } from 'components';
import { DELETEreq, GETreq } from 'api';
import { AppCtx } from 'store';
import { RESET_APP_STATE } from 'store/context';
import { useResetAtoms } from 'store/atoms';

const SettingsScr = ({ navigation }: any) => {
  const { top } = useSafeAreaInsets();
  const { t } = useT();
  const [isDelete, setIsDelete] = useState(false);
  const [isLogout, setIsLogout] = useState(false);
  const { dispatch } = useContext(AppCtx);
  const { showLoader, hideLoader } = useLoader();
  const resetAtoms = useResetAtoms();
  const { showSnackbar } = useSnackbar();

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

  const deleteUser = async () => {
    try {
      showLoader();
      const { data } = await DELETEreq('profile/delete_account');
      console.log('Delete data::', JSON.stringify(data, null, 3));
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
    <View style={[styles.container, { paddingTop: top + 12 }]}>
      <View style={styles.row}>
        <TouchableOpacity
          style={styles.backBtn}
          onPress={() => navigation.goBack()}
        >
          <BACK_BTN_IC />
        </TouchableOpacity>
        <Text allowFontScaling={false} style={styles.title}>{t('SETTINGS')}</Text>
      </View>

      <View style={styles.menuContainer}>
        <SettingsMenuItem
          icon={<LOCK_IC />}
          title={t('CHANGE_PASSWORD')}
          onPress={() => {
            navigation.navigate('ChangePasswordScr');
          }}
        />
        <SettingsMenuItem
          icon={<LOGOUT_ICON />}
          title={t('LOGOUT')}
          onPress={() => {
            setIsLogout(true);
          }}
        />
        <SettingsMenuItem
          icon={<DUSTBIN_IC />}
          title={t('DELETE_ACCOUNT')}
          onPress={() => {
            setIsDelete(true);
          }}
        />
      </View>

      <DeleteConfirmationModal
        visible={isDelete}
        title={t('DELETE_ACCOUNT')}
        message={t('DELETE_ACCOUNT_MSG')}
        cancelText={t('NO')}
        confirmText={t('YES')}
        onClose={() => {
          setIsDelete(false);
        }}
        onConfirm={deleteUser}
        cancelTextCol={_COL.PRIMARY_RED}
      />

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

export default SettingsScr;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.WHITE,
  },
  backBtn: {
    position: 'absolute',
    left: 16,
    zIndex: 1,
  },
  title: {
    fontSize: 18,
    flex: 1,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.FINAL_BLACK,
    textAlign: 'center',
    top: isIOS ? 4 : 2,
  },
  row: {
    flexDirection: 'row',
    borderBottomWidth: 1,
    borderBottomColor: _COL.BORDER,
    paddingBottom: isIOS ? 18 : 12,
  },
  menuContainer: {
    paddingHorizontal: 20,
    marginTop: 8,
  },
  menuItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 16,
    borderBottomWidth: 1,
    borderBottomColor: _COL.BORDER_FOURTH,
  },
  menuLeft: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  menuTitle: {
    fontSize: 14,
    color: _COL.FINAL_BLACK,
    fontFamily: FONT.MEDIUM,
    marginLeft: 16,
    marginTop: 2,
  },
});
