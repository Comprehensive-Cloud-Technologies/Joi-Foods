import { StyleSheet } from 'react-native';
import { FontS } from 'function';
import { _COL, _HEIGHT, _W } from 'utils/constants';
import compSty from './compSty';
import orderSummarySty from './orderSummarySty';
import itemDetailsSty from './itemDetailsSty';
import orderDetailsSty from './orderDetailsSty';

const sty = StyleSheet.create({
  HW100: {
    height: '100%',
    width: '100%',
  },
  f1: { flex: 1 },
  f1W100: {
    flex: 1,
    width: '100%',
  },
  f1WH100: {
    flex: 1,
    width: '100%',
    height: '100%',
  },
  ctrF1: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  rowCtr: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
  },
  ctrF1wh100: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    width: '100%',
    height: '100%',
  },
  f1CtrW100: {
    alignSelf: 'center',
    flex: 1,
    width: '100%',
    justifyContent: 'center',
    alignItems: 'center',
  },
  ctrF1w100: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    width: '100%',
  },
  Row100Jsb: {
    flexDirection: 'row',
    width: '100%',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  ctr: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  rowAc: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  JcSa: {
    justifyContent: 'space-around',
  },
  f1HW100ctrBG: {
    flex: 1,
    height: '100%',
    width: '100%',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: _COL.BG,
  },

  mB3: { marginBottom: 30 },
  mT3: { marginTop: '3%' },
  mT4: { marginTop: '4%' },
  mT5: { marginTop: '5%' },
  mt12: { marginTop: FontS(12) },
  mV3: { marginVertical: '3%' },
  mV5: { marginVertical: '5%' },
  mV10: { marginVertical: '10%' },
  mV15: { marginVertical: '15%' },
  p3: { padding: '3%' },
  p4: { padding: '4%' },
  p5: { padding: '5%' },
  P4: { padding: _W * 0.04 },
  pH5: { paddingHorizontal: '5%' },
  pH10: { paddingHorizontal: '10%' },
  mV3h: { marginVertical: _HEIGHT * 0.03 },
  mB10: { marginBottom: '10%' },
  _m8: { margin: -8 },
  mH5: { marginHorizontal: '5%' },
  mH9: { marginHorizontal: '9%' },
  br99: { borderRadius: 99 },
  mT50: { marginTop: '50%' },
  pL0: { paddingLeft: 0 },
  pL2: { paddingLeft: 2 },
  mL10: { marginLeft: 10 },
  mL15: { marginLeft: 15 },
});

export {
  sty,
  compSty,
  orderSummarySty,
  itemDetailsSty,
  orderDetailsSty
};
