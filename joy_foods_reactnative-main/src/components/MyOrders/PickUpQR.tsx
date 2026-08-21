import { StyleProp, StyleSheet, Text, TouchableOpacity, View, ViewStyle } from 'react-native';
import QRCode from 'react-native-qrcode-svg';
import { useT } from 'internationalization';
import { _COL, FONT } from 'utils';

const PickUpQR = ({
  pickUpCode,
  qrData,
  onPress,
  vSty
}: {
  pickUpCode: string;
  qrData: string;
  onPress: () => void;
  vSty?: StyleProp<ViewStyle>;
}) => {
  const { t } = useT();
  return (
    <View style={[styles.pickupQRContainer,vSty]}>
      <View>
        <Text allowFontScaling={false} style={styles.pickupQRText}>{t('PICKUP_QR')}</Text>
        <Text allowFontScaling={false} style={styles.pickupItemText}>{t('SHOW_PICKUP_ITEMS')}</Text>
        <Text allowFontScaling={false} style={styles.pickupCodeText}>
          {t('YOUR_PICKUP_CODE_IS')}
          <Text allowFontScaling={false} style={styles.pickupCode}>{pickUpCode}</Text>
        </Text>
      </View>
      <TouchableOpacity
        onPress={onPress}
        activeOpacity={0.8}
        style={styles.qrCodeContainer}
      >
        <QRCode value={qrData} size={50} />
      </TouchableOpacity>
    </View>
  );
};

export default PickUpQR;

const styles = StyleSheet.create({
  pickupQRContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 15,
    marginHorizontal: 24,
    marginTop: 24,
    paddingVertical: 15,
    backgroundColor: _COL.SECONDARY_BG,
    borderRadius: 6,
  },
  qrCodeContainer: {
    padding: 9,
    backgroundColor: _COL.QR_BG,
    borderRadius: 8,
  },
  pickupQRText: {
    fontSize: 14,
    fontFamily: FONT.BOLD,
    color: _COL.MAIN_BLACK,
  },
  pickupItemText: {
    fontSize: 12,
    fontFamily: FONT.REGULAR,
    color: _COL.TEXT_GREY,
    marginTop: 2,
  },
  pickupCodeText: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    color: _COL.MAIN_BLACK,
    marginTop: 8,
  },
  pickupCode: {
    fontSize: 14,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.MAIN_BLACK,
  },
});
