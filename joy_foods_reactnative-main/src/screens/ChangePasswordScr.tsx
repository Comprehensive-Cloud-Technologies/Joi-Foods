import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import React, { useRef, useState } from 'react';
import { _COL, _H, _WIDTH, FONT, isIOS } from 'utils';
import InputField, { DynamicInputRef } from 'components/ui/InputField';
import { useT } from 'internationalization';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { StackProps } from 'types';
import { BTN, useSnackbar } from 'components';
import { BACK_BTN_IC, LOCK_IC } from 'assets';
import { POSTreq } from 'api';

const ChangePasswordScr = ({ navigation }: StackProps<'ChangePasswordScr'>) => {
  const { t } = useT();

  const insets = useSafeAreaInsets();
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const currentRef = useRef<DynamicInputRef>(null);
  const newRef = useRef<DynamicInputRef>(null);
  const confirmRef = useRef<DynamicInputRef>(null);
  const { showSnackbar } = useSnackbar();

  const handleSave = async () => {
    let hasError = false;
    if (!currentPassword.trim()) {
      currentRef.current?.setError(true, t('PLS_FILL_THE_INPUT'));
      hasError = true;
    } else {
      currentRef.current?.clearError();
    }
    if (!newPassword.trim()) {
      newRef.current?.setError(true, t('PLS_FILL_THE_INPUT'));
      hasError = true;
    } else {
      newRef.current?.clearError();
    }
    if (!confirmPassword.trim()) {
      confirmRef.current?.setError(true, t('PLS_FILL_THE_INPUT'));
      hasError = true;
    } else if (newPassword.trim() !== confirmPassword.trim()) {
      confirmRef.current?.setError(true, t('PASSWORDS_DO_NOT_MATCH'));
      hasError = true;
    } else {
      confirmRef.current?.clearError();
    }
    if (hasError) {
      return;
    }
    try {
      const payload = {
        current_password: currentPassword.trim(),
        new_password: newPassword.trim(),
        confirm_password: confirmPassword.trim(),
      };
      const { success, data } = await POSTreq(
        'profile/change_password',
        payload,
        true,
      );
      console.log(
        'Change Password Response::',
        success,
        JSON.stringify(data, null, 3),
      );
      if (success) {
        navigation.goBack();
      }
      showSnackbar(data.message);
    } catch (error) {
      console.log(error);
    }
  };

  return (
    <View style={styles.container}>
      <TouchableOpacity
        style={{ paddingTop: insets.top + 19, alignSelf: 'flex-start' }}
        onPress={() => navigation.goBack()}
      >
        <BACK_BTN_IC />
      </TouchableOpacity>
      <View style={styles.titleContainer}>
        <Text allowFontScaling={false} style={styles.title}>{t('CHANGE_PASSWORD')}</Text>
        <Text allowFontScaling={false} style={styles.subtitle}>
          {t('ENTER_CURRENT_PASSWORD_CREATE_NEW_ONE')}
        </Text>
      </View>
      <View style={styles.divider} />
      <InputField
        label={t('CURRENT_PASSWORD')}
        placeholder={t('PLACEHOLDER_CURRENT_PASSWORD')}
        leftIcon={<LOCK_IC />}
        value={currentPassword}
        type="password"
        onChangeText={text => setCurrentPassword(text)}
        isGradient={false}
        autoCapitalize="none"
        ref={currentRef}
      />
      <InputField
        label={t('NEW_PASSWORD')}
        placeholder={t('PLACEHOLDER_NEW_PASSWORD')}
        leftIcon={<LOCK_IC />}
        type="password"
        value={newPassword}
        onChangeText={text => setNewPassword(text)}
        isGradient={false}
        autoCapitalize="none"
        containerStyle={{ marginTop: 16 }}
        ref={newRef}
      />
      <InputField
        label={t('CONFIRM_PASSWORD')}
        placeholder={t('PLACEHOLDER_CONFIRM_PASSWORD')}
        leftIcon={<LOCK_IC />}
        type="password"
        value={confirmPassword}
        onChangeText={text => setConfirmPassword(text)}
        isGradient={false}
        autoCapitalize="none"
        containerStyle={{ marginTop: 16 }}
        ref={confirmRef}
      />

      <BTN title={t('SAVE')} onP={handleSave} borderR={120} mTop={24} />
    </View>
  );
};

export default ChangePasswordScr;

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
    marginTop: isIOS?8:2,
  },
  divider: {
    height: 1,
    backgroundColor: _COL.BORDER,
    marginTop: 16,
    marginBottom:18
  },
  titleContainer: {
    marginTop: _H * 0.04,
  },
});
