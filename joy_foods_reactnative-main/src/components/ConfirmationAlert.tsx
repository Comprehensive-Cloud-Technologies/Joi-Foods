import React, { forwardRef, useImperativeHandle, useState } from 'react';
import { StyleSheet, Text, TouchableOpacity, View, Modal, Dimensions, } from 'react-native';
import Animated, { FadeIn, FadeInDown, FadeOut, SlideOutDown, useAnimatedProps, useAnimatedStyle, withTiming } from 'react-native-reanimated';
import { tT } from 'internationalization';
import { compSty, sty } from 'styles';
import { CLOSE_IC } from 'assets';
import Txt1L from './ui/Txt1L';
import BTN from './ui/BTN';
import { _COL } from 'utils';

interface IProps {
  t: (str: tT) => string;
  onConfirm: () => void;
  onCancel?: () => void;
}

type ConfirmationAlertRefT = {
  open: (title: string, message?: string, lBtn?: tT, rBtn?: tT) => void;
  close: () => void;
};

const { width: screenWidth, height: screenHeight } = Dimensions.get('window');

const ConfirmationAlert = forwardRef<ConfirmationAlertRefT, IProps>(
  ({ t, onConfirm, onCancel }, ref) => {
    const [active, setActive] = useState(false);
    const [{ title, message, lBtn, rBtn }, setTitleMsg] = useState({
      title: '',
      message: ``,
      lBtn: t('NO'),
      rBtn: t('YES'),
    });

    function onOpen(title: string, message?: string, lBtn?: tT, rBtn?: tT) {
      setTitleMsg({
        title,
        message: message || `${t('ARE_YOU_SURE_YOU_WANT_TO')} ${title}?`,
        lBtn: t(lBtn || 'NO'),
        rBtn: t(rBtn || 'YES'),
      });
      setTimeout(() => {
        setActive(true);
      }, 10);
    }
    function onClose() {
      setActive(false);
      setTimeout(() => {
        setTitleMsg({ title: '', message: ``, lBtn: t('NO'), rBtn: t('YES') });
      }, 10);
    }

    useImperativeHandle(ref, () => ({ close: onClose, open: onOpen }), [
      active,
      title,
      message,
      lBtn,
      rBtn,
    ]);

    const backdropStyle = useAnimatedStyle(
      () => ({ opacity: withTiming(active ? 1 : 0) }),
      [active],
    );
    const animatedProps = useAnimatedProps(
      () => ({ pointerEvents: active ? ('auto' as const) : ('none' as const) }),
      [active],
    );

    function onP() {
      onClose();
      onConfirm();
    }

    if (!active) return null;

    return (
      <Modal
        transparent
        visible={active}
        animationType="none"
        onRequestClose={onClose}
        statusBarTranslucent
      >
        <Animated.View
          entering={FadeIn}
          exiting={FadeOut.delay(300)}
          style={[styles.fullScreenBackdrop, { backgroundColor: _COL.BLACK04 }]}
        >
          {/* Full screen backdrop for outside tap */}
          <TouchableOpacity
            style={styles.backdropTouchable}
            onPress={onClose}
            activeOpacity={1}
          />

          {/* Modal content */}
          <Animated.View
            entering={FadeInDown}
            style={[compSty.confirmationAlertV, { zIndex: 1000 }]}
            exiting={SlideOutDown.duration(500)}
          >
            <TouchableOpacity onPress={onClose} activeOpacity={1} style={{
              position: "absolute",
              top: 10,
              right: 10,
            }}>
              <CLOSE_IC />
            </TouchableOpacity>
            <Txt1L sty={compSty.confirmationAlertTitle}>{title}</Txt1L>
            <Text style={compSty.confirmationAlertMsg}>{message}</Text>
            <View style={[sty.Row100Jsb, sty.mT5, {}]}>
              <BTN
                title={lBtn}
                // width="48%"
                onP={() => {
                  onCancel && onCancel();
                  onClose();
                }}
              />
              {/* {rBtn && (
                <BTN title={rBtn} width="48%" bordered secondary onP={onP} />
              )} */}
            </View>
          </Animated.View>

          {/* Another backdrop layer for outside tap */}
          <TouchableOpacity
            style={styles.backdropTouchable}
            onPress={onClose}
            activeOpacity={1}
          />
        </Animated.View>
      </Modal>
    );
  },
);

export default ConfirmationAlert;

export type { ConfirmationAlertRefT };

const styles = StyleSheet.create({
  fullScreenBackdrop: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    width: screenWidth,
    height: screenHeight,
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 999999999,
  },
  backdropTouchable: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    width: screenWidth,
    height: screenHeight,
    zIndex: 999,
  },
});
