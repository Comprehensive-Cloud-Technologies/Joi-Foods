import TextButton from 'components/TextButton';
import BottomActionSheet from 'components/ui/BottomActionSheet';
import BTN from 'components/ui/BTN';
import InputField, { DynamicInputRef } from 'components/ui/InputField';
import { useT } from 'internationalization';
import { memo, useRef, useState } from 'react';
import { ScrollView, StyleSheet, Text, TouchableOpacity } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { _COL, FONT } from 'utils';

const CancelOrderSheet = ({
  isVisible,
  setIsVisible,
  onProceed,
}: {
  isVisible: boolean;
  setIsVisible: () => void;
  onProceed: (reason: string) => void;
}) => {
  const { t } = useT();
  const { bottom } = useSafeAreaInsets();
  const [reason, setReason] = useState('');
  const reasonRef = useRef<DynamicInputRef>(null);


  return (
    <BottomActionSheet
      visible={isVisible}
      onClose={setIsVisible}
      showHandle={true}
    >
      <ScrollView
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}
        contentContainerStyle={{
          paddingHorizontal: 21,
        }}
      >
        <Text allowFontScaling={false} style={styles.cancelHeading}>{t('CANCEL_ORDER')}</Text>
        <Text allowFontScaling={false} style={styles.cancelDesc}>{t('CANCEL_ORDER_DESC')}</Text>

        <InputField
          value={reason}
          onChangeText={setReason}
          label={t('REASON')}
          multiline
          placeholder={t('ENTER_CANCEL_ORDER_REASON')}
          inputContainerStyle={{ paddingHorizontal: 16 }}
          containerStyle={{ marginTop: 28 }}
          ref={reasonRef}
        />
        <BTN
          title={t('SUBMIT')}
          onP={() => {
            if (reason.length < 10) {
              reasonRef.current?.setError(
                true,
                'Please enter minimum 10 characters',
              );
              return;
            }
            reasonRef.current?.clearError();
            onProceed(reason);
          }}
          borderR={120}
          mTop={30}
        />
        <TextButton
          onP={setIsVisible}
          isUnderline={true}
          style={{ paddingBottom: bottom }}
          text={t('CANCEL')}
        />

      </ScrollView>
    </BottomActionSheet>
  );
};

export default memo(CancelOrderSheet);

const styles = StyleSheet.create({
  cancelHeading: {
    fontSize: 22,
    fontFamily: FONT.BOLD,
    color: _COL.FINAL_BLACK,
    textAlign: 'left',
    marginTop: 27,
    lineHeight: 30,
  },
  cancelDesc: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    color: _COL.MAIN_BLACK,
    textAlign: 'left',
    marginTop: 10,
    lineHeight: 24,
  },
});
