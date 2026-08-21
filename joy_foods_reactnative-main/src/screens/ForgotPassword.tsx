import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import React, { useRef, useState } from 'react';
import { _COL, _WIDTH, EmailRegex, FONT } from 'utils';
import {
  BTN,
  DynamicInputRef,
  InputField,
  useLoader,
  useSnackbar,
} from 'components';
import { useT } from 'internationalization';
import { StackProps } from 'types';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { POSTreq } from 'api';
import { useAtom } from 'jotai';
import { companyCodeAtom } from 'store/atoms';
import { BACK_BTN_IC, EMAIL_IC } from 'assets';

const ForgotPasswordScreen = ({
  navigation,
}: StackProps<'ForgotPasswordScreen'>) => {
  const { t } = useT();
  const [email, setEmail] = useState<string>('');
  const emailRef = useRef<DynamicInputRef>(null);
  const { showLoader, hideLoader } = useLoader();
  const { showSnackbar } = useSnackbar();
  const insets = useSafeAreaInsets();
  const [companyCodeData] = useAtom(companyCodeAtom);

  const handleSubmit = async () => {
    let hasError = false;
    if (!email.trim()) {
      emailRef.current?.setError(true, t('PLS_FILL_THE_INPUT'));
      hasError = true;
    } else if (!EmailRegex.test(email)) {
      emailRef.current?.setError(true, t('PLS_ETR_VALID_EMAIL'));
      hasError = true;
    } else {
      emailRef.current?.clearError();
    }
    if (hasError) {
      return;
    }
    try {
      showLoader();
      const payload = {
        email: email,
        company_id: companyCodeData?.id,
      };
      const { success, code, data } = await POSTreq(
        'auth/forgot_password',
        payload,
        true,
      );
      if (success) {
        showSnackbar(data?.message, 'success');
        navigation.navigate('VerifyOtpScreen', {
          email: /* data?.data?.email ?? */ email,
        });
      } else {
        showSnackbar(data?.message, 'error');
      }
      console.log(
        'Forgot Password Response::',
        success,
        code,
        JSON.stringify(data, null, 3),
      );
    } catch (error) {
      console.log('Error::', error);
    } finally {
      hideLoader();
    }
  };

  return (
    <View style={styles.container}>
      <TouchableOpacity
        style={{ marginTop: insets.top + 19 }}
        onPress={() => {
          navigation.goBack();
        }}
      >
        <BACK_BTN_IC />
      </TouchableOpacity>

      <View style={styles.titleContainer}>
        <Text allowFontScaling={false} style={styles.title}>{t('FORGOT_PASSWORD_TITLE')}</Text>
        <Text allowFontScaling={false} style={styles.subtitle}>{t('ENTER_EMAIL_RESET_PASSWORD')}</Text>
      </View>
      <View style={styles.divider} />
      <InputField
        label={t('EMAIL')}
        placeholder={t('PLACEHOLDER_EMAIL')}
        leftIcon={<EMAIL_IC />}
        value={email}
        onChangeText={text => setEmail(text)}
        isGradient={false}
        autoCapitalize="none"
        ref={emailRef}
      />

      <BTN title={t('SUBMIT')} onP={handleSubmit} borderR={120} mTop={24} />
    </View>
  );
};

export default ForgotPasswordScreen;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.WHITE,
    paddingHorizontal: _WIDTH * 0.06,
  },
  title: {
    fontSize: 24,
    fontFamily: FONT.BOLD,
    color: _COL.FINAL_BLACK,
  },
  subtitle: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    color: _COL.MAIN_BLACK,
  },
  titleContainer: {
    marginTop: 43,
    gap: 5,
  },
  divider: {
    height: 1,
    backgroundColor: _COL.BORDER,
    marginVertical: 18,
  },
});
