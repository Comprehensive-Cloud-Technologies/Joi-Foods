import React from 'react';
import { StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { _COL, FONT, isIOS } from 'utils';
import { StackProps } from 'types';
import { BACK_BTN_IC } from 'assets';
import WebView from 'react-native-webview';

const CMSController = ({
  navigation,
  route: {
    params: { title, content },
  },
}: StackProps<'CMSController'>) => {
  const insets = useSafeAreaInsets();
  return (
    <View style={[styles.container, { paddingTop: insets.top + 12 }]}>
      <View style={styles.row}>
        <TouchableOpacity
          style={styles.backBtn}
          onPress={() => navigation.goBack()}
        >
          <BACK_BTN_IC />
        </TouchableOpacity>
        <Text allowFontScaling={false} style={styles.title}>{title}</Text>
      </View>
      <WebView source={{ uri: content }} startInLoadingState />
    </View>
  );
};

export default CMSController;

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
  title: {
    fontSize: 18,
    flex: 1,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.FINAL_BLACK,
    textAlign: 'center',
    top: isIOS ? 4 : 2,
  },
  row: {
    flexDirection: 'row',
    borderBottomWidth: 1,
    borderBottomColor: _COL.BORDER,
    paddingBottom: isIOS ? 18 : 12,
  },
});
