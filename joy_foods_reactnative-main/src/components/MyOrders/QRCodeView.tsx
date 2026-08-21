import BottomActionSheet from 'components/ui/BottomActionSheet';
import BTN from 'components/ui/BTN';
import { StyleSheet, Text, View } from 'react-native';
import { useT } from 'internationalization';
import QRCode from 'react-native-qrcode-svg';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { OrderDetailsT, OrderSuccessT } from 'types';
import { _COL, FONT } from 'utils';

const QRCodeView = ({
  isPickUpQR,
  setIsPickUpQR,
  qrData,
  pickupCode,
}: {
  isPickUpQR: boolean;
  setIsPickUpQR: Function;
  qrData: string;
  pickupCode: string;
}) => {
  const { t } = useT();
  const { bottom } = useSafeAreaInsets();
  return (
    <BottomActionSheet
      visible={isPickUpQR}
      onClose={() => {
        setIsPickUpQR(false);
      }}
      showHandle={true}
    >
      <View style={styles.showQRContainer}>
        <Text allowFontScaling={false} style={styles.showQRHeading}>{t('PICKUP_QR')}</Text>
        <Text allowFontScaling={false} style={styles.showQRSubheading}>{t('SHOW_PICKUP_ITEMS')}</Text>
        <View style={styles.showQR}>
          <QRCode value={qrData} size={200} />
        </View>
        <Text allowFontScaling={false} style={styles.pickupCodeText}>{t('YOUR_PICKUP_CODE_IS')}</Text>
        <Text allowFontScaling={false} style={styles.showPickupCode}>{pickupCode}</Text>
        <BTN
          title={t('CLOSE')}
          onP={() => {
            setIsPickUpQR(false);
          }}
          borderR={64}
          mTop={23}
          mBottom={bottom + 16}
        />
      </View>
    </BottomActionSheet>
  );
};

export default QRCodeView;

const styles = StyleSheet.create({
  pickupCodeText: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    color: _COL.MAIN_BLACK,
    lineHeight: 28,
    marginTop: 29,
  },
  showQRContainer: {
    alignItems: 'center',
    paddingHorizontal: 20,
  },
  showQRHeading: {
    fontSize: 22,
    fontFamily: FONT.BOLD,
    color: _COL.FINAL_BLACK,
    textAlign: 'center',
    marginTop: 27,
    lineHeight: 28,
  },
  showQRSubheading: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    color: _COL.MAIN_BLACK,
    textAlign: 'center',
    lineHeight: 24,
    marginTop: 10,
  },
  showQR: {
    marginTop: 22,
    backgroundColor: _COL.SECONDARY_BG,
    padding: 14,
    borderRadius: 4,
  },
  showPickupCode: {
    fontSize: 18,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.MAIN_BLACK,
    lineHeight: 28,
  },
});
