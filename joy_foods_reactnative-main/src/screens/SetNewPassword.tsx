import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import React, { useRef, useState } from 'react';
import { _COL, _H, _WIDTH, FONT } from 'utils';
import InputField, { DynamicInputRef } from 'components/ui/InputField';
import { useT } from 'internationalization';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { navigateOrGoBackTo, StackProps } from 'types';
import { BTN, useLoader, useSnackbar } from 'components';
import { POSTreq } from 'api';
import { companyCodeAtom } from 'store/atoms';
import { useAtom } from 'jotai';
import { BACK_BTN_IC, LOCK_IC } from 'assets';

const SetNewPasswordScreen = ({
  navigation,
  route: {
    params: { reset_token, email },
  },
}: StackProps<'SetNewPasswordScreen'>) => {
  const { t } = useT();

  const insets = useSafeAreaInsets();
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const passwordRef = useRef<DynamicInputRef>(null);
  const confirmPasswordRef = useRef<DynamicInputRef>(null);
  const [companyCodeData] = useAtom(companyCodeAtom);
  const { showLoader, hideLoader } = useLoader();
  const { showSnackbar } = useSnackbar();

  const handleUpdate = async () => {
    let hasError = false;
    if (!password.trim()) {
      passwordRef.current?.setError(true, t('PLS_FILL_THE_INPUT'));
      hasError = true;
    } else {
      passwordRef.current?.clearError();
    }
    if (!confirmPassword.trim()) {
      confirmPasswordRef.current?.setError(true, t('PLS_FILL_THE_INPUT'));
      hasError = true;
    } else if (password !== confirmPassword) {
      confirmPasswordRef.current?.setError(true, t('PASSWORD_NOT_MATCHED'));
      hasError = true;
    } else {
      confirmPasswordRef.current?.clearError();
    }
    if (hasError) {
      return;
    }
    try {
      showLoader();
      const payload = {
        email,
        company_id: companyCodeData?.id,
        reset_token,
        password,
        confirm_password: confirmPassword,
      };
      const { success, data } = await POSTreq(
        'auth/reset_password',
        payload,
        true,
      );
      console.log(
        'Reset Password Response::',
        success,
        JSON.stringify(data, null, 3),
      );
      showSnackbar(data?.message);

      if (success) {
        navigateOrGoBackTo(navigation, 'LoginScreen');
      }
    } catch (e) {
      console.log(e);
    } finally {
      hideLoader();
    }
  };

  return (
    <View style={styles.container}>
      <TouchableOpacity
        style={{ paddingTop: insets.top + 19 }}
        onPress={() => navigation.goBack()}
      >
        <BACK_BTN_IC />
      </TouchableOpacity>
      <View style={styles.titleContainer}>
        <Text allowFontScaling={false} style={styles.title}>{t('SET_NEW_PASSWORD')}</Text>
        <Text allowFontScaling={false} style={styles.subtitle}>{t('CREATE_NEW_PASSWORD_SECURITY')}</Text>
      </View>
      <View style={styles.divider} />
      <InputField
        label={t('NEW_PASSWORD')}
        placeholder={t('PLACEHOLDER_ENTER_PASSWORD')}
        leftIcon={<LOCK_IC />}
        value={password}
        type="password"
        onChangeText={text => setPassword(text)}
        isGradient={false}
        autoCapitalize="none"
        ref={passwordRef}
      />
      <InputField
        label={t('REPEAT_NEW_PASSWORD')}
        placeholder={t('PLACEHOLDER_ENTER_PASSWORD')}
        leftIcon={<LOCK_IC />}
        type="password"
        value={confirmPassword}
        onChangeText={text => setConfirmPassword(text)}
        isGradient={false}
        autoCapitalize="none"
        containerStyle={{ marginTop: 16 }}
        ref={confirmPasswordRef}
      />

      <BTN title={t('UPDATE')} onP={handleUpdate} borderR={120} mTop={24} />
    </View>
  );
};

export default SetNewPasswordScreen;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.WHITE,
    paddingHorizontal: 24,
  },
  title: {
    fontSize: 24,
    fontFamily: FONT.BOLD,
    color: _COL.BLACK,
  },
  subtitle: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    color: _COL.MAIN_BLACK,
    lineHeight: 22,
    marginTop: 12,
  },
  divider: {
    height: 1,
    backgroundColor: _COL.BORDER,
    marginVertical: 18,
  },
  titleContainer: {
    marginTop: _H * 0.04,
  },
});
