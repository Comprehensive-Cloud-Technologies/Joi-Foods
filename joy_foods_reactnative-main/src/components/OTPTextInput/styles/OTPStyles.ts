import { FontS } from 'function';
import { StyleSheet } from 'react-native';
import { FONT, _COL } from 'utils';

const OTPStyles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  textInput: {
    width: "14%",
    minHeight: 50,
    borderWidth: 1.5,
    margin: 5,
    textAlign: 'center',
    fontSize: FontS(18),
    paddingVertical: 15,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.BLACK,
    borderRadius:8
  }
});

export default OTPStyles;
