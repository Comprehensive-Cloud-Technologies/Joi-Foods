import { SUCCESSFULL_IC } from 'assets';
import { BTN } from 'components';
import { useT } from 'internationalization';
import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { StackProps } from 'types';
import { _COL, FONT } from 'utils';

const SupportSuccessScr = ({
  navigation,
  route: {
    params: { message },
  },
}: StackProps<'SupportSuccessScr'>) => {
  const { t } = useT();
  return (
    <View
      style={{
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
        paddingHorizontal: 24,
      }}
    >
      <SUCCESSFULL_IC style={{}} />
      <Text allowFontScaling={false} style={styles.title}>{t('REQUEST_SUBMITTED')}</Text>
      <Text allowFontScaling={false} style={styles.subtitle}>{message}</Text>
      <BTN
        title={t('DONE')}
        onP={() => navigation.popToTop()}
        mTop={40}
        rounded
        borderR={56}
      />
    </View>
  );
};

export default SupportSuccessScr;

const styles = StyleSheet.create({
  title: {
    fontFamily: FONT.BOLD,
    fontSize: 24,
    color: _COL.FINAL_BLACK,
    textAlign: 'center',
    marginTop: 20,
  },
  subtitle: {
    fontFamily: FONT.REGULAR,
    fontSize: 14,
    color: _COL.TEXT_GREY,
    lineHeight: 20,
    textAlign: 'center',
    marginTop: 7,
    marginHorizontal: 8,
  },
});
