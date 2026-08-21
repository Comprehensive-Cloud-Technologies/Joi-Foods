import { View, Text, TouchableOpacity, StyleSheet, KeyboardAvoidingView, ScrollView, Pressable } from 'react-native';
import React, { useEffect, useRef, useState } from 'react';
import { _COL, _H, _W, _WIDTH, EmailRegex, FONT, initialCompanyCode, isIOS } from 'utils';
import InputField, { DynamicInputRef } from 'components/ui/InputField';
import { useT } from 'internationalization';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { POSTreq } from 'api';
import { accessTokenAtom, companyCodeAtom, userDataAtom, useResetAtoms } from 'store/atoms';
import { useAtom } from 'jotai';
import { useLoader, useSnackbar } from 'components';
import { StackProps } from 'types';
import { EMAIL_IC, LOCK_IC } from 'assets';
import { messaging } from 'utils/firebase';

const LoginScreen = ({ navigation }: StackProps<'LoginScreen'>) => {

  const { t } = useT();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const insets = useSafeAreaInsets();
  const [companyCodeData, setCompanyCodeData] = useAtom(companyCodeAtom);
  const { showLoader, hideLoader } = useLoader();
  const { showSnackbar } = useSnackbar();
  const [, setUserData] = useAtom(userDataAtom);
  const [, setAccessToken] = useAtom(accessTokenAtom);
  const emailRef = useRef<DynamicInputRef>(null);
  const pwdRef = useRef<DynamicInputRef>(null);
  const resetAtoms = useResetAtoms();

  const handleLogin = async () => {
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
    if (!password.trim()) {
      pwdRef.current?.setError(true, t('PLS_FILL_THE_INPUT'));
      hasError = true;
    } else {
      pwdRef.current?.clearError();
    }
    if (hasError) {
      return;
    }
    try {
      showLoader();
      const payload = {
        email,
        password,
        company_id: companyCodeData.id,
      };

      const { data, success, error } = await POSTreq('auth/login', payload, true);
      console.log('Login Response::', success, error, JSON.stringify(data, null, 3));
      if (success) {
        setUserData(data?.data?.employee);
        setAccessToken(data?.data?.token);
      } else {
        showSnackbar(data.message);
      }
      hideLoader();
    } catch (e) {
      console.log(e);
    }
  };

  const handleForgotPassword = () => {
    navigation.navigate('ForgotPasswordScreen');
  };

  const handleCreateAccount = () => {
    navigation.navigate('CreateAccount');
  };

  const handleChangeCompany = () => {
    messaging()
      .unsubscribeFromTopic(companyCodeData.company_code)
      .then(() => console.log('Unsubscribed from ' + companyCodeData.company_code + ' topic!'))
      .catch(error => console.log('Error unsubscribing from ' + companyCodeData.company_code + ' topic:', error));
    setCompanyCodeData(initialCompanyCode);
    resetAtoms();
  };

  const subscribeTopic = () => {
    messaging()
      .subscribeToTopic(companyCodeData.company_code)
      .then(() => console.log('Subscribed to ' + companyCodeData.company_code + ' topic!'))
      .catch(error => console.log('Error subscribing to ' + companyCodeData.company_code + ' topic:', error));
  }

  useEffect(() => {
    if (companyCodeData.company_code) {
      subscribeTopic();
    }
  }, [companyCodeData.company_code]);

  return (
    <View style={styles.container}>
      <ScrollView
        showsVerticalScrollIndicator={false}
        keyboardShouldPersistTaps="handled"
      >
        <KeyboardAvoidingView
          behavior={isIOS ? 'padding' : undefined}
        >
          <View
            style={{ paddingTop: insets.top + 94 }}
          >
            <Text allowFontScaling={false} style={styles.title}>{t('WELCOME')}</Text>
            <Text allowFontScaling={false} style={styles.subtitle}>
              {t('ENTER_COMPANY_CREDENTIAL_LOGIN')}
            </Text>
          </View>

          <View style={styles.divider} />

          <View>
            <View style={styles.inputWrapper}>
              <InputField
                label={t('ENTER_EMAIL_EMPLOYER_ID')}
                placeholder={t('PLACEHOLDER_EMAIL_EMPLOYER_ID')}
                leftIcon={<EMAIL_IC />}
                value={email}
                onChangeText={text => setEmail(text)}
                isGradient={false}
                inputStyle={styles.inputText}
                autoCapitalize="none"
                keyboardType="email-address"
                ref={emailRef}
              />
            </View>

            <View style={styles.inputWrapper}>
              <InputField
                label={t('PASSWORD')}
                placeholder={t('PLACEHOLDER_PASSWORD')}
                leftIcon={<LOCK_IC />}
                type="password"
                value={password}
                onChangeText={text => setPassword(text)}
                isGradient={false}
                inputStyle={styles.inputText}
                autoCapitalize="none"
                ref={pwdRef}
              />
            </View>

            <TouchableOpacity
              onPress={handleForgotPassword}
              style={styles.forgotPasswordContainer}
            >
              <Text allowFontScaling={false} style={styles.forgotPasswordText}>
                {t('FORGOT_PASSWORD')}
              </Text>
            </TouchableOpacity>
          </View>

          <View style={styles.buttonSection}>
            <TouchableOpacity
              style={styles.loginButton}
              onPress={handleLogin}
              activeOpacity={0.8}
            >
              <Text allowFontScaling={false} style={styles.loginButtonText}>{t('LOGIN')}</Text>
            </TouchableOpacity>

            <View style={styles.createAccountContainer}>
              <Text allowFontScaling={false} style={styles.createAccountText}>
                {t('DONT_HAVE_ACCOUNT')}
              </Text>
              <TouchableOpacity onPress={handleCreateAccount}>
                <Text allowFontScaling={false} style={styles.createAccountLink}>
                  {t('CREATE_ACCOUNT')}
                </Text>
              </TouchableOpacity>
            </View>
          </View>
        </KeyboardAvoidingView>
      </ScrollView>
      <View style={{ backgroundColor: _COL.LIGHT_BG, padding: 16, borderRadius: 12, marginBottom: insets.bottom }}>
        <Text allowFontScaling={false} style={{ fontSize: 14, fontFamily: FONT.MEDIUM, lineHeight: 20, color: _COL.TEXT_GREY }}>{t('YOU_CURRENTLY_WITH')}</Text>
        <View style={{ flexDirection: "row", justifyContent: "space-between", marginTop: 5 }}>
          <Text allowFontScaling={false} style={{ fontSize: 14, fontFamily: FONT.SEMI_BOLD, lineHeight: 20, color: _COL.FINAL_BLACK }}>{companyCodeData.company_name}</Text>
          <Pressable onPress={handleChangeCompany}>
            <Text allowFontScaling={false} style={{ color: _COL.PRIMARY_RED, fontFamily: FONT.SEMI_BOLD, fontSize: 12, textDecorationLine: "underline" }}>{t('CHANGE')}</Text>
          </Pressable>
        </View>
      </View>
    </View>
  );
};

export default LoginScreen;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.WHITE,
    padding: _W * 0.06,
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
  inputWrapper: {
    marginBottom: 10,
  },
  divider: {
    height: 1,
    backgroundColor: _COL.BORDER,
    marginVertical: 18,
  },

  inputText: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    color: _COL.BLACK,
  },
  forgotPasswordContainer: {
    alignSelf: 'center',
    marginTop: 14,
  },
  forgotPasswordText: {
    fontSize: 14,
    fontFamily: FONT.MEDIUM,
    color: _COL.SECONDARY_ORANGE,
    textDecorationLine: 'underline',
  },
  buttonSection: {
    marginTop: 24,
    flex: 1
  },
  loginButton: {
    backgroundColor: _COL.PRIMARY_RED,
    borderRadius: 56,
    height: _H * 0.06,
    justifyContent: 'center' as const,
    alignItems: 'center' as const,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  loginButtonText: {
    fontSize: 14,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.WHITE,
  },
  createAccountContainer: {
    flexDirection: 'row' as const,
    justifyContent: 'center' as const,
    alignItems: 'center' as const,
    marginTop: 28,
  },
  createAccountText: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
  },
  createAccountLink: {
    fontSize: 14,
    fontFamily: FONT.SEMI_BOLD,
    textDecorationLine: 'underline',
  },
  error: {
    color: _COL.RED,
  },
  changeCompanyLink: {
    fontSize: 14,
    fontFamily: FONT.SEMI_BOLD,
    textDecorationLine: 'underline',
  },
  changeCompany: {
    alignSelf: 'center',
    marginTop: 26,
  },
});
