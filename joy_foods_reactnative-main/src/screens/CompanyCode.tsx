import {
  View,
  Text,
  StyleSheet,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import React, { useRef, useState } from 'react';
import { _COL, _W, FONT } from 'utils';
import { useT } from 'internationalization';
import { POSTreq } from 'api';
import { OTPTextInput, OTPTextInputHandle } from 'components/OTPTextInput';
import { useAtom } from 'jotai';
import { companyCodeAtom } from 'store/atoms';
import { BTN, useLoader, useSnackbar } from 'components';

const CompanyCode = () => {
  const { t } = useT();
  const [otp, setOtp] = useState('');
  const OTPRef = useRef<OTPTextInputHandle | null>(null);
  const [companyCodeData, setCompanyCodeData] = useAtom(companyCodeAtom);
  const { showLoader, hideLoader } = useLoader();
  const { showSnackbar } = useSnackbar();

  const onSubmit = async () => {
    try {
      showLoader();
      const { success, data, code } = await POSTreq(
        'auth/verify_company',
        {
          company_code: otp,
        },
        true,
      );
      console.log(
        'Company code response::',
        success,
        code,
        JSON.stringify(data, null, 3),
      );
      if (success) {
        setCompanyCodeData(data?.data);
      } else {
        showSnackbar(data?.message, 'error');
      }
    } catch (error) {
      console.log(error);
    } finally {
      hideLoader();
    }
  };

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      keyboardVerticalOffset={Platform.OS === 'ios' ? 64 : 0}
    >
      <View style={{ marginHorizontal: 24 }}>
        <View style={styles.titleContainer}>
          <Text allowFontScaling={false} style={styles.title}>{t('COMPANY_CODE')}</Text>
          <Text allowFontScaling={false} style={styles.subtitle}>{t('ENTER_COMPANY_CODE')}</Text>
        </View>
        <View style={styles.divider} />
        <Text allowFontScaling={false} style={styles.codeTitle}>{t('COMPANY_CODE')}</Text>

        <OTPTextInput
          editable
          autoFocus
          rKeyT="done"
          ref={OTPRef}
          inputCount={6}
          keyboardType="default"
          txtCol={_COL.BLACK}
          onTextChangeHandler={setOtp}
          tintColor={_COL.BLACK}
          offTintColor={_COL.LIGHT_GRAY}
          containerStyle={{ width: '100%' }}
          textInputStyle={{ maxWidth: _W * 0.15 }}
        />
        <BTN title={t('SUBMIT')} onP={onSubmit} borderR={120} mTop={24} />
      </View>
    </KeyboardAvoidingView>
  );
};

export default CompanyCode;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.WHITE,
  },
  title: {
    fontSize: 24,
    fontFamily: FONT.BOLD,
    color: _COL.TEXT_BLACK_DARK,
  },
  subtitle: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    color: _COL.TEXT_BLACK,
  },
  titleContainer: {
    marginTop: 139,
    gap: 5,
  },
  divider: {
    height: 1,
    backgroundColor: _COL.BORDER,
    marginVertical: 18,
  },
  codeTitle: {
    fontSize: 14,
    fontFamily: FONT.SEMI_BOLD,
    marginBottom: 10,
    color: _COL.FINAL_BLACK,
  },
});
