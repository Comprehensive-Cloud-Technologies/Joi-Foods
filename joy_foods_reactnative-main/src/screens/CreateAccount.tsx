import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  Platform,
} from 'react-native';
import React, { useContext, useRef, useState } from 'react';
import { _COL, _H, _W, _WIDTH, EmailRegex, FONT, isIOS } from 'utils';
import InputField, { DynamicInputRef } from 'components/ui/InputField';
import { useT } from 'internationalization';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { POSTreq } from 'api';
import { useAtom } from 'jotai';
import { accessTokenAtom, companyCodeAtom, userDataAtom } from 'store/atoms';
import { useLoader, useSnackbar } from 'components';
import BTN from 'components/ui/BTN';
import { StackProps } from 'types';
import { BACK_BTN_IC, CHECK_BOX_IC, EMAIL_IC, LOCK_IC, USER_IC } from 'assets';
import { AppCtx } from 'store';
import { KeyboardAvoidingView } from 'react-native-keyboard-controller'



const CreateAccount = ({ navigation }: StackProps<'CreateAccount'>) => {

  const { t } = useT();
  const insets = useSafeAreaInsets();
  const [form, setForm] = useState({
    firstName: '',
    lastName: '',
    email: '',
    password: '',
    confirmPassword: '',
    agreeToTerms: false,
  });
  const { dispatch, state: { miscData } } = useContext(AppCtx);
  const firstNameRef = useRef<DynamicInputRef>(null);
  const lastNameRef = useRef<DynamicInputRef>(null);
  const emailRef = useRef<DynamicInputRef>(null);
  const pwdRef = useRef<DynamicInputRef>(null);
  const confirmPwdRef = useRef<DynamicInputRef>(null);
  const [companyCodeData] = useAtom(companyCodeAtom);
  const { showLoader, hideLoader } = useLoader();
  const { showSnackbar } = useSnackbar();
  const [userData, setUserData] = useAtom(userDataAtom);
  const [accessToken, setAccessToken] = useAtom(accessTokenAtom);

  const handleSignUp = async () => {
    let hasError = false;
    if (!form.firstName.trim()) {
      firstNameRef.current?.setError(true, t('PLS_FILL_THE_INPUT'));
      hasError = true;
    } else {
      firstNameRef.current?.clearError();
    }
    if (!form.lastName.trim()) {
      lastNameRef.current?.setError(true, t('PLS_FILL_THE_INPUT'));
      hasError = true;
    } else {
      lastNameRef.current?.clearError();
    }
    if (!form.email.trim()) {
      emailRef.current?.setError(true, t('PLS_FILL_THE_INPUT'));
      hasError = true;
    } else if (!EmailRegex.test(form.email)) {
      emailRef.current?.setError(true, t('PLS_ETR_VALID_EMAIL'));
      hasError = true;
    } else {
      emailRef.current?.clearError();
    }
    if (!form.password.trim()) {
      pwdRef.current?.setError(true, t('PLS_FILL_THE_INPUT'));
      hasError = true;
    } else {
      pwdRef.current?.clearError();
    }
    if (!form.confirmPassword.trim()) {
      confirmPwdRef.current?.setError(true, t('PLS_FILL_THE_INPUT'));
      hasError = true;
    } else if (form.password !== form.confirmPassword) {
      confirmPwdRef.current?.setError(true, t('PASSWORD_NOT_MATCHED'));
      hasError = true;
    } else {
      confirmPwdRef.current?.clearError();
    }
    if (hasError) {
      return;
    }
    try {
      showLoader();
      const payload = {
        first_name: form.firstName,
        last_name: form.lastName,
        email: form.email,
        password: form.password,
        company_id: companyCodeData?.id,
      };

      const { success, code, data } = await POSTreq(
        'auth/signup',
        payload,
        true,
      );
      console.log(
        'Sign Up Response::',
        success,
        code,
        JSON.stringify(data, null, 3),
      );
      showSnackbar(data.message);
      if (success) {
        setUserData(data?.data?.employee);
        setAccessToken(data?.data?.token);
      }
      hideLoader();
    } catch (error) {
      console.log(error);
    }
  };

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={'padding'}
      keyboardVerticalOffset={isIOS ? 120 : 100}
    >
      <View>
        <TouchableOpacity
          style={[styles.backBtn, { marginTop: insets.top + 19 }]}
          onPress={() => {
            navigation.goBack();
          }}
        >
          <BACK_BTN_IC />
        </TouchableOpacity>

        <ScrollView
          showsVerticalScrollIndicator={false}
          contentContainerStyle={{ paddingBottom: 200 }}
        >
          <View style={styles.titleContainer}>
            <Text allowFontScaling={false} style={styles.title}>{t('CREATE_ACCOUNT')}</Text>
            <Text allowFontScaling={false} style={styles.subtitle}>
              {t('ENTER_COMPANY_CREDENTIAL_LOGIN_DETAILS')}
            </Text>
          </View>
          <View style={styles.divider} />
          <View style={styles.inputSection}>
            <InputField
              label={t('FIRST_NAME')}
              placeholder={t('ETR_FIRST_NAME')}
              leftIcon={<USER_IC />}
              value={form.firstName}
              onChangeText={text => setForm({ ...form, firstName: text })}
              isGradient={false}
              inputContainerStyle={styles.inputContainer}
              containerStyle={{ flex: 1 }}
              inputStyle={styles.inputText}
              autoCapitalize="none"
              ref={firstNameRef}
            />
            <InputField
              label={t('LAST_NAME')}
              placeholder={t('ETR_LAST_NAME')}
              leftIcon={<USER_IC />}
              value={form.lastName}
              onChangeText={text => setForm({ ...form, lastName: text })}
              isGradient={false}
              inputContainerStyle={styles.inputContainer}
              containerStyle={{ flex: 1 }}
              inputStyle={styles.inputText}
              autoCapitalize="none"
              ref={lastNameRef}
            />
            <InputField
              label={t('EMAIL')}
              placeholder={t('PLACEHOLDER_EMAIL')}
              leftIcon={<EMAIL_IC />}
              value={form.email}
              onChangeText={text => setForm({ ...form, email: text })}
              isGradient={false}
              inputContainerStyle={styles.inputContainer}
              inputStyle={styles.inputText}
              autoCapitalize="none"
              ref={emailRef}
            />
            <InputField
              label={t('PASSWORD')}
              placeholder={t('PLACEHOLDER_ENTER_PASSWORD')}
              leftIcon={<LOCK_IC />}
              value={form.password}
              type="password"
              onChangeText={text => setForm({ ...form, password: text })}
              isGradient={false}
              inputContainerStyle={styles.inputContainer}
              inputStyle={styles.inputText}
              autoCapitalize="none"
              ref={pwdRef}
            />
            <InputField
              label={t('REPEAT_NEW_PASSWORD')}
              placeholder={t('PLACEHOLDER_ENTER_PASSWORD')}
              leftIcon={<LOCK_IC />}
              type="password"
              value={form.confirmPassword}
              onChangeText={text =>
                setForm({ ...form, confirmPassword: text })
              }
              isGradient={false}
              inputContainerStyle={styles.inputContainer}
              inputStyle={styles.inputText}
              autoCapitalize="none"
              ref={confirmPwdRef}
            />

          </View>

          <View style={styles.row}>
            <TouchableOpacity
              style={styles.checkboxContainer}
              onPress={() => {
                setForm({ ...form, agreeToTerms: !form.agreeToTerms });
              }}
            >
              <CHECK_BOX_IC isChecked={form.agreeToTerms} />
            </TouchableOpacity>
            <View style={{ flexDirection: 'column', marginLeft: 12 }}>
              <Text allowFontScaling={false} style={styles.checkboxText}>{t('BY_SIGNING_UP')}</Text>
              <View style={{ flexDirection: 'row' }}>
                <TouchableOpacity
                  onPress={() => {
                    navigation.navigate('CMSController', { title: 'Terms & Conditions', content: miscData?.terms_and_conditions ?? "" });
                  }}
                >
                  <Text allowFontScaling={false} style={styles.termsConditions}>
                    {t('TERMS_CONDITIONS')}{' '}
                  </Text>
                </TouchableOpacity>

                <Text allowFontScaling={false} style={styles.andText}> {t('AND')} </Text>
                <TouchableOpacity
                  onPress={() => {
                    navigation.navigate('CMSController', { title: 'Privacy Policy', content: miscData?.privacy_policy ?? "" });
                  }}
                >
                  <Text allowFontScaling={false} style={styles.termsConditions}>
                    {' '}
                    {t('PRIVACY_POLICY')}
                  </Text>
                </TouchableOpacity>
              </View>
            </View>
          </View>

          <BTN
            title={t('SIGNUP')}
            onP={handleSignUp}
            isDisabled={!form.agreeToTerms}
            borderR={120}
            mTop={24}
          />

          <View style={styles.loginContainer}>
            <Text allowFontScaling={false} style={styles.loginPrompt}>{t('ALREADY_HAVE_ACCOUNT')}</Text>
            <TouchableOpacity
              onPress={() => {
                navigation.goBack();
              }}
            >
              <Text allowFontScaling={false} style={styles.loginText}> {t('LOGIN')}</Text>
            </TouchableOpacity>
          </View>
        </ScrollView>
      </View>
    </KeyboardAvoidingView>
  );
};

export default CreateAccount;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.WHITE,
    paddingHorizontal: _W * 0.06,
  },
  row: {
    flexDirection: 'row',
    marginTop: 16
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
  },
  divider: {
    height: 1,
    backgroundColor: _COL.BORDER,
    marginVertical: 18,
  },
  inputSection: {
    gap: 12,
  },
  inputContainer: {
    backgroundColor: _COL.WHITE,
  },
  inputText: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    color: _COL.BLACK,
  },
  checkboxContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    // marginTop: 30,
  },
  checkbox: {
    width: 24,
    height: 24,
    borderWidth: 2,
    borderColor: _COL.TEXT_GREY_LIGHT,
    borderRadius: 4,
    backgroundColor: _COL.WHITE,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 10,
  },
  checkboxChecked: {
    backgroundColor: _COL.BLUE,
  },
  checkmark: {
    color: _COL.WHITE,
    fontSize: 18,
    fontFamily: FONT.BOLD,
  },
  checkboxText: {
    fontSize: 12,
    color: _COL.CHECK_BOX,
    fontFamily: FONT.LIGHT,
  },
  andText: {
    fontSize: 12,
    color: _COL.CHECK_BOX,
    fontFamily: FONT.LIGHT,
  },
  termsConditions: {
    textDecorationLine: 'underline',
    fontFamily: FONT.MEDIUM,
    fontSize: 12,
  },
  loginContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginTop: _H * 0.03,
  },
  loginPrompt: {
    fontSize: 14,
    color: _COL.FINAL_BLACK,
    fontFamily: FONT.LIGHT,
  },
  loginText: {
    fontFamily: FONT.SEMI_BOLD,
    marginLeft: 5,
    textDecorationLine: 'underline',
  },
});
