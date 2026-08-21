import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  Pressable,
  AppState,
  AppStateStatus,
} from 'react-native';
import { useEffect, useRef, useState } from 'react';
import { _COL, _H, _W, _WIDTH, FONT } from 'utils';
import React from 'react';
import { useT } from 'internationalization';
import { StackProps } from 'types';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { OTPTextInput, OTPTextInputHandle } from 'components/OTPTextInput';
import { BTN, useLoader, useSnackbar } from 'components';
import { POSTreq } from 'api';
import { companyCodeAtom } from 'store/atoms';
import { useAtom } from 'jotai';
import { BACK_BTN_IC } from 'assets';

const VerifyOtpScreen = ({
  navigation,
  route,
}: StackProps<'VerifyOtpScreen'>) => {
  const { t } = useT();

  const email = route.params.email;
  const [otp, setOtp] = useState('');
  const OTPRef = useRef<OTPTextInputHandle | null>(null);
  const [timer, setTimer] = useState(59);
  const insets = useSafeAreaInsets();
  const [companyCodeData] = useAtom(companyCodeAtom);
  const { showLoader, hideLoader } = useLoader();
  const { showSnackbar } = useSnackbar();

  const [endTime, setEndTime] = useState(Date.now() + 59000);

  useEffect(() => {
    const updateTimer = () => {
      const remaining = Math.max(0, Math.ceil((endTime - Date.now()) / 1000));
      setTimer(remaining);
    };

    updateTimer();

    const timerID = setInterval(updateTimer, 1000);

    return () => clearInterval(timerID);
  }, [endTime]);

  useEffect(() => {
    const subscription = AppState.addEventListener('change', (nextAppState: AppStateStatus) => {
      if (nextAppState === 'active') {
        const remaining = Math.max(0, Math.ceil((endTime - Date.now()) / 1000));
        setTimer(remaining);
      }
    });

    return () => subscription.remove();
  }, [endTime]);

  const verifyOtp = async () => {
    if (otp.length !== 6) {
      showSnackbar(t('PLS_FILL_THE_INPUT'), 'error');
      return;
    }
    try {
      showLoader();
      const payload = {
        email,
        otp,
        company_id: companyCodeData.id,
      };
      const { success, code, data } = await POSTreq(
        'auth/verify_otp',
        payload,
        true,
      );
      console.log(
        'Verify Otp Response::',
        success,
        code,
        JSON.stringify(data, null, 3),
      );
      if (success) {
        navigation.navigate('SetNewPasswordScreen', {
          reset_token: data?.data?.reset_token, email
        });
      } else {
        showSnackbar(data?.message);
      }
    } catch (error) {
      console.log('Verify Otp Error::', error);
    } finally {
      hideLoader();
    }
  };

  const resendOtp = async () => {
    if (timer !== 0) {
      return;
    }
    try {
      showLoader();
      const payload = {
        email,
        company_id: companyCodeData.id,
      };
      const { success, code, data } = await POSTreq(
        'auth/forgot_password',
        payload,
        true,
      );
      console.log(
        'Resend Otp Response::',
        success,
        code,
        JSON.stringify(data, null, 3),
      );
      if (success) {
        setEndTime(Date.now() + 59000);
        showSnackbar('OTP Resent Successfully', 'success');
      }
    } catch (error) {
      console.log('Resend Otp Error::', error);
    } finally {
      hideLoader();
    }
  };

  return (
    <View style={styles.container}>
      <TouchableOpacity
        style={[styles.backBtn, { marginTop: insets.top + 19 }]}
        onPress={() => navigation.goBack()}
      >
        <BACK_BTN_IC />
      </TouchableOpacity>

      <View style={styles.titleContainer}>
        <Text allowFontScaling={false} style={styles.title}>{t('VERIFY_OTP')}</Text>
        <Text allowFontScaling={false} style={styles.subtitle}>
          {t('CODE_SENT_TO')}
          <Text allowFontScaling={false} style={{ fontFamily: FONT.SEMI_BOLD }}> {email}</Text>
        </Text>
      </View>
      <View style={styles.divider} />
      <OTPTextInput
        editable
        autoFocus
        rKeyT="done"
        ref={OTPRef}
        inputCount={6}
        keyboardType="numeric"
        txtCol={_COL.BLACK}
        onTextChangeHandler={setOtp}
        tintColor={_COL.BLACK}
        offTintColor={_COL.BORDER_FIFTH}
        containerStyle={{ width: '100%' }}
        textInputStyle={{ maxWidth: _W * 0.15 }}
      />

      <BTN title={t('VERIFY')} onP={verifyOtp} borderR={120} mTop={24} />

      <Pressable onPress={resendOtp} style={{ marginTop: 30 }}>
        {timer == 0 ? (
          <Text allowFontScaling={false}
            style={[
              styles.timer,
              { color: _COL.SECONDARY_ORANGE, textDecorationLine: 'underline' },
            ]}
          >
            {t('RESEND_CODE')}
          </Text>
        ) : (
          <Text allowFontScaling={false} style={styles.timer}>
            00:{timer < 10 ? `0${timer}` : timer}
          </Text>
        )}
      </Pressable>
    </View>
  );
};

export default VerifyOtpScreen;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.WHITE,
    paddingHorizontal: _W * 0.06,
  },
  backBtn: {
    marginTop: _H * 0.02,
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
    marginTop: _H * 0.04,
    gap: 5,
  },
  timer: {
    textAlign: 'center',
    fontSize: 14,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.TEXT_BLACK_DARK,
  },
  divider: {
    height: 1,
    backgroundColor: _COL.BORDER,
    marginTop: 18,
    marginBottom: 24,
  },
});
