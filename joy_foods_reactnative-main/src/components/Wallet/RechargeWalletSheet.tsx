import BottomActionSheet from 'components/ui/BottomActionSheet';
import BTN from 'components/ui/BTN';
import InputField from 'components/ui/InputField';
import { useT } from 'internationalization';
import { memo, useState } from 'react';
import {
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { _COL, FONT } from 'utils';

const RechargeWalletSheet = ({
  isVisible,
  setIsVisible,
  onProceed,
}: {
  isVisible: boolean;
  setIsVisible: () => void;
  onProceed: (amount: string) => void;
}) => {
  const { t } = useT();
  const { bottom } = useSafeAreaInsets();
  const [amount, setAmount] = useState('');

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
        <Text allowFontScaling={false} style={styles.feedbackHeading}>{t('RECHARGE')}</Text>

        <InputField
          value={amount}
          onChangeText={setAmount}
          label={t('ENTER_AMOUNT')}
          placeholder={"₹0.00"}
          inputContainerStyle={{ paddingHorizontal: 16 }}
          keyboardType="numeric"
          containerStyle={{ marginTop: 20 }}
        />
        <BTN title={t('PROCEED')} onP={() => onProceed(amount)} borderR={120} mTop={20} />
        <TouchableOpacity
          onPress={setIsVisible}
          style={[{ marginBottom: bottom + 16, alignSelf: 'center' }]}
        >
          <Text allowFontScaling={false} style={styles.cancelBtnText}>{t('CANCEL')}</Text>
        </TouchableOpacity>
      </ScrollView>
    </BottomActionSheet>
  );
};

export default memo(RechargeWalletSheet);

const styles = StyleSheet.create({
  cancelBtnText: {
    fontSize: 14,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.FINAL_BLACK,
    alignItems: 'center',
    marginTop: 18,
    textDecorationLine: 'underline',
    textDecorationStyle: 'solid',
  },
  feedbackHeading: {
    fontSize: 22,
    fontFamily: FONT.BOLD,
    color: _COL.FINAL_BLACK,
    textAlign: 'left',
    marginTop: 27,
    lineHeight: 30,
  },
});
