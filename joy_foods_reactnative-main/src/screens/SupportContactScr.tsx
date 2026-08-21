import { Alert, StyleSheet, Text, TouchableOpacity, View, Linking } from 'react-native'
import React, { useEffect, useRef, useState } from 'react'
import { BACK_BTN_IC, EMAIL2_IC, PHONE_IC } from 'assets'
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { _COL, FONT, isIOS } from 'utils/constants';
import { useT } from 'internationalization';
import { StackProps, SupportContactT } from 'types';
import { GETreq } from 'api';
import { BTN } from 'components';
import ConfirmationAlert, { ConfirmationAlertRefT } from 'components/ConfirmationAlert';

const SupportContactScr = ({ navigation }: StackProps<'SupportContactScr'>) => {

  const { top } = useSafeAreaInsets();
  const { t } = useT();
  const [contactInfo, setContactInfo] = useState<SupportContactT>();
  const alertRef = useRef<ConfirmationAlertRefT>(null);


  const getContactInfo = async () => {
    try {
      const { data, success } =
        await GETreq('support/topics');
      if (success) {
        console.log('Contact info::', JSON.stringify(data, null, 3));
        setContactInfo(data.data);
      } else {
        alertRef.current?.open('Info', data?.message || 'Failed to get contact info', "OK");
        // Alert.alert('Info', data?.message || 'Failed to get contact info');
      }
    } catch (error) {
      console.log(error);
    }
  }

  useEffect(() => {
    getContactInfo();
  }, []);

  const handleEmailPress = () => {
    if (contactInfo?.support_email) {
      Linking.openURL(`mailto:${contactInfo.support_email}`)
        .catch(err => alertRef.current?.open('Info', 'Failed to open email client', "OK"));
    }
  };

  const handlePhonePress = () => {
    if (contactInfo?.support_phone) {
      const deviceOS = isIOS ? 'telprompt' : 'tel';
      Linking.openURL(`${deviceOS}:${contactInfo.support_phone}`)
        .catch(err => alertRef.current?.open('Info', 'Failed to open phone dialer', "OK"));
    }
  };


  return (
    <View style={[styles.container, { paddingTop: top + 12 }]}>
      <View style={styles.row}>
        <TouchableOpacity
          style={styles.backBtn}
          onPress={() => navigation.goBack()}
        >
          <BACK_BTN_IC />
        </TouchableOpacity>
        <Text allowFontScaling={false} style={styles.title}>{t('SUPPORT')}</Text>
      </View>

      <Text allowFontScaling={false} style={styles.needHelpTxt}>
        {t('NEED_HELP')}
      </Text>
      <Text allowFontScaling={false} style={styles.contactSupportTxt}>
        {t('CONTACT_SUPPORT')}
      </Text>

      <View style={styles.contactContainer}>
        <EMAIL2_IC />
        <View style={styles.column}>
          <Text allowFontScaling={false} style={styles.contactTitle}>{t('EMAIL')}</Text>
          <TouchableOpacity onPress={handleEmailPress}>
            <Text allowFontScaling={false} style={[styles.contactInfo, {
              textDecorationLine: 'underline',
              textDecorationStyle: 'solid',
            }]}>{contactInfo?.support_email}</Text>
          </TouchableOpacity>
        </View>
      </View>

      <View style={styles.contactContainer}>
        <PHONE_IC style={{ marginTop: 4 }} />
        <View style={styles.column}>
          <Text allowFontScaling={false} style={styles.contactTitle}>{t('PHONE_NUMBER')}</Text>
          <TouchableOpacity onPress={handlePhonePress}>
            <Text allowFontScaling={false} style={styles.contactInfo}>
              {contactInfo?.support_phone && `+91 ${contactInfo.support_phone}`}
            </Text>
          </TouchableOpacity>
        </View>
      </View>

      <View style={styles.btnContainer}>
        <BTN
          title={t('REQUEST_INQUIRY')}
          onP={() => navigation.navigate('SupportScr', { topics: contactInfo?.topics ?? [] })}
          mTop={30}
          borderR={56}
        />
      </View>
      <ConfirmationAlert
        t={t}
        ref={alertRef}
        onConfirm={() => { }}
      />

    </View>
  )
}

export default SupportContactScr

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.WHITE,
  },
  backBtn: {
    position: 'absolute',
    left: 16,
    zIndex: 1,
  },
  row: {
    flexDirection: 'row',
    borderBottomWidth: 1,
    borderBottomColor: _COL.BORDER,
    paddingBottom: isIOS ? 18 : 12,
  },
  title: {
    fontSize: 18,
    flex: 1,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.FINAL_BLACK,
    textAlign: 'center',
    top: isIOS ? 4 : 2,
  },
  needHelpTxt: {
    fontSize: 16,
    color: _COL.FINAL_BLACK,
    fontFamily: FONT.SEMI_BOLD,
    marginTop: 56,
    textAlign: 'center',
  },
  contactSupportTxt: {
    fontSize: 14,
    color: _COL.TEXT_GREY,
    fontFamily: FONT.REGULAR,
    marginTop: 9,
    marginBottom: 12,
    textAlign: 'center',
  },
  contactContainer: {
    flexDirection: 'row',
    paddingHorizontal: 17,
    paddingTop: 16,
    paddingBottom: 18,
    marginHorizontal: 26,
    borderRadius: 12,
    backgroundColor: _COL.SECOND_LAYOUT_BG,
    marginTop: 13,
  },
  column: {
    marginLeft: 14,
  },
  contactTitle: {
    fontSize: 14,
    color: _COL.MAIN_BLACK,
    fontFamily: FONT.SEMI_BOLD,
  },
  contactInfo: {
    fontSize: 14,
    color: _COL.MAIN_BLACK,
    fontFamily: FONT.REGULAR,
    marginTop: 4,
  },
  btnContainer: {
    paddingHorizontal: 24,
  }

})