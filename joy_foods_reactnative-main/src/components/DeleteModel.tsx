import React, { useEffect, useRef, useState } from 'react';
import {
  Modal,
  View,
  Text,
  Pressable,
  StyleSheet,
  Animated,
  StyleProp,
  ViewStyle,
  ColorValue,
} from 'react-native';

import { useT } from 'internationalization';
import { _COL, FONT, isIOS } from 'utils';
import BTN from './ui/BTN';


interface DeleteConfirmationModalProps {
  visible: boolean;
  onClose: () => void;
  onConfirm: () => void;
  title: string;
  message: string;
  confirmText: string;
  cancelText: string;
  cancelTextCol?: ColorValue;
  btnConSty?: StyleProp<ViewStyle>;
  onCancel?: () => void;
  disableClose?: boolean;
}

const DeleteConfirmationModal: React.FC<DeleteConfirmationModalProps> = ({
  visible,
  title,
  message,
  cancelText,
  confirmText,
  cancelTextCol,
  onClose,
  onConfirm,
  btnConSty,
  onCancel,
  disableClose = false
}) => {
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const scaleAnim = useRef(new Animated.Value(0.9)).current;
  const [isRendered, setIsRendered] = useState(false);
  const { t, lng } = useT();
  const isRTL = lng === 'ar';


  useEffect(() => {
    if (visible) {
      if (!isIOS) {
        setTimeout(() => setIsRendered(true), 50);
      } else {
        setIsRendered(true);
      }

      Animated.parallel([
        Animated.timing(fadeAnim, {
          toValue: 1,
          duration: 250,
          useNativeDriver: true,
        }),
        Animated.spring(scaleAnim, {
          toValue: 1,
          tension: 50,
          friction: 7,
          useNativeDriver: true,
        }),
      ]).start();
    } else {
      setIsRendered(false);
      fadeAnim.setValue(0);
      scaleAnim.setValue(0.9);
    }
  }, [visible]);

  const handleClose = () => {
    Animated.parallel([
      Animated.timing(fadeAnim, {
        toValue: 0,
        duration: 200,
        useNativeDriver: true,
      }),
      Animated.timing(scaleAnim, {
        toValue: 0.9,
        duration: 200,
        useNativeDriver: true,
      }),
    ]).start(() => {
      setIsRendered(false);
      onClose?.();
    });
  };

  const handleConfirm = () => {
    handleClose();
    setTimeout(() => onConfirm?.(), 300);
  };

  if (!visible) return null;

  return (
    <Modal
      visible={visible}
      transparent
      statusBarTranslucent
      animationType="none"
      onRequestClose={!disableClose ? handleClose : undefined}
    >
      <Animated.View
        style={[
          styles.overlay,
          {
            opacity: fadeAnim,
          },
        ]}
      >
        {/* {isRendered && (
          <BlurView
            key={`overlay-blur-light`}
            style={StyleSheet.absoluteFill}
            blurType={isIOS ? 'ultraThinMaterialDark' : 'light'}
            blurAmount={20}
            overlayColor="rgba(0,0,0,0.4)"
            reducedTransparencyFallbackColor="rgba(0, 0, 0, 0.7)"
            blurRadius={25}
          />
        )} */}
        <View
          style={[
            StyleSheet.absoluteFill,
            {
              backgroundColor: 'rgba(0, 0, 0, 0.6)',
            },
          ]}
        />


      </Animated.View>

      <Pressable style={StyleSheet.absoluteFill} onPress={!disableClose ? handleClose : undefined} />

      <View style={styles.container} pointerEvents="box-none">
        <Animated.View
          style={[
            styles.alertBox,
            {
              opacity: fadeAnim,
              transform: [{ scale: scaleAnim }],
            },
          ]}
        >
          {/* {isRendered && (
            <BlurView
              key={`content-blur-light`}
              style={StyleSheet.absoluteFill}
              blurType={"light"}
              blurAmount={20}
              overlayColor={'rgba(255, 255, 255, 0.5)'}
              reducedTransparencyFallbackColor="rgba(255, 255, 255, 0.1)"
              blurRadius={25}
            />
          )} */}



          <View style={styles.content}>
            <Text allowFontScaling={false}
              style={styles.title}
            >
              {title}
            </Text>

            <Text allowFontScaling={false}
              style={styles.message}
            >
              {message}
            </Text>

            <View
              style={[
                styles.buttonContainer,
                {
                  flexDirection: isRTL ? 'row-reverse' : 'row',
                  justifyContent: 'space-between',
                },
                btnConSty,
              ]}
            >
              {!disableClose &&
                <BTN
                  title={cancelText}
                  secondary
                  bordered
                  borderCol={_COL.PRIMARY_RED}
                  borderR={48}
                  bgCol={_COL.WHITE}
                  tCol={cancelTextCol}
                  width={'49%'}
                  onP={() => {
                    onCancel?.();
                    handleClose();
                  }}
                />
              }
              <BTN
                title={confirmText}
                width={disableClose ? '100%' : '49%'}
                borderR={48}
                bgCol={_COL.PRIMARY_RED}
                onP={handleConfirm}
              />
            </View>
          </View>
        </Animated.View>
      </View>
    </Modal>
  );
};
const styles = StyleSheet.create({
  overlay: {
    ...StyleSheet.absoluteFillObject,
  },
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 16,
  },
  alertBox: {
    width: '100%',
    maxWidth: 400,
    borderRadius: 24,
    overflow: 'hidden',
  },
  content: {
    paddingHorizontal: 20,
    paddingBottom: 20,
    paddingTop: 40,
    backgroundColor: _COL.WHITE,
  },
  title: {
    fontSize: 22,
    fontFamily: FONT.SEMI_BOLD,
    textAlign: 'center',
    color: _COL.FINAL_BLACK,
  },
  message: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    marginTop: 8,
    textAlign: 'center',
    color: _COL.MAIN_BLACK,
    paddingHorizontal: 47,
  },
  buttonContainer: {
    flexDirection: 'row',
    marginTop: 24,
  },
  button: {
    flex: 1,
    paddingVertical: 12,
    borderRadius: 48,
    justifyContent: 'center',
    alignItems: 'center',
  },
  buttonText: {
    fontSize: 14,
    fontFamily: FONT.BOLD,
    lineHeight: 24,
  },
});

export default DeleteConfirmationModal;
