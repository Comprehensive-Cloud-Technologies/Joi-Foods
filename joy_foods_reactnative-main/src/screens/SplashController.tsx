import React, { useContext, useEffect } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { JOI_SPLASH_ICON } from '../assets';
import { _COL, FONT, isIOS } from 'utils';
import { useT } from 'internationalization';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { set_hide_splash } from 'store/context/Actions';
import CTX from 'store/context';
import { messaging } from 'utils/firebase';

const SplashController = () => {
  const { t } = useT();
  const { bottom } = useSafeAreaInsets();
  const { dispatch } = useContext(CTX);

  useEffect(() => {
    messaging()
      .subscribeToTopic("joi_foods")
      .then(() => console.log("Subscribed to joi_foods topic!"))
      .catch(error => console.log("Error subscribing to joi_foods topic:", error));

    setTimeout(() => {
      set_hide_splash(dispatch, true);
    }, 3000);
  }, []);

  return (
    <View
      style={{
        backgroundColor: _COL.BG,
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
      }}
    >
      <JOI_SPLASH_ICON />
      <Text allowFontScaling={false} style={[styles.txtSty, { bottom: bottom + 50 }]}>
        {t('CORPORATE_CATERING')}
      </Text>
    </View>
  );
};

export default SplashController;

const styles = StyleSheet.create({
  txtSty: {
    fontFamily: FONT.BOLD,
    fontSize: 24,
    color: _COL.TEXT_BLACK,
    position: 'absolute',
  },
});
