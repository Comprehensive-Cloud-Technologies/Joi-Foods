import React, { useEffect, useCallback } from 'react';
import { View, Modal, StyleSheet, TouchableOpacity, Text } from 'react-native';
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  withTiming,
  runOnJS,
} from 'react-native-reanimated';
import {
  GestureHandlerRootView,
  GestureDetector,
  Gesture,
} from 'react-native-gesture-handler';
import { _COL, _HEIGHT, FONT, isIOS } from 'utils';
import { KeyboardAvoidingView } from 'react-native-keyboard-controller';
import { SafeAreaView } from 'react-native-safe-area-context';

interface CustomBottomSheetProps {
  visible: boolean;
  onClose: () => void;
  children: React.ReactNode;
  showCloseButton?: boolean;
  title?: string;
  showDivider?: boolean;
  showHandle?: boolean;
}

const BottomActionSheet = ({
  visible,
  onClose,
  children,
  showCloseButton = false,
  title = '',
  showDivider = true,
  showHandle = false,
}: CustomBottomSheetProps) => {
  const translateY = useSharedValue(_HEIGHT);

  const slideUp = useCallback(() => {
    translateY.value = withTiming(0, { duration: 500 });
  }, []);

  const slideDown = useCallback(() => {
    translateY.value = withTiming(_HEIGHT, { duration: 500 }, finished => {
      'worklet';
      if (finished) {
        runOnJS(onClose)();
      }
    });
  }, [onClose]);

  useEffect(() => {
    if (visible) {
      slideUp();
    } else {
      slideDown();
    }
  }, [visible, slideUp, slideDown]);

  const panGesture = Gesture.Pan()
    .activeOffsetY(10)
    .onUpdate(event => {
      'worklet';
      // Only allow dragging down
      const newTranslateY = event.translationY;
      if (newTranslateY >= 0) {
        translateY.value = newTranslateY;
      }
    })
    .onEnd(event => {
      'worklet';
      // If dragged down more than 100px or velocity is high, close the sheet
      if (event.translationY > 100 || event.velocityY > 500) {
        translateY.value = withTiming(_HEIGHT, { duration: 300 }, finished => {
          'worklet';
          if (finished) {
            runOnJS(onClose)();
          }
        });
      } else {
        // Otherwise, snap back to open position
        translateY.value = withTiming(0, { duration: 300 });
      }
    });

  const animatedStyle = useAnimatedStyle(() => {
    return {
      transform: [{ translateY: translateY.value }],
    };
  });

  const handleClose = () => {
    slideDown();
  };

  const Container = isIOS ? View : SafeAreaView;

  return (
    <Modal
      transparent={true}
      visible={visible}
      statusBarTranslucent
      onRequestClose={handleClose}
      animationType="none"
    >
      <KeyboardAvoidingView
        behavior={isIOS ? 'padding' : 'height'}
        style={{ flex: 1 }}
        keyboardVerticalOffset={isIOS ? 0 : 0}
        enabled={true}
      >
        <GestureHandlerRootView style={{ flex: 1 }}>
          <Container style={{ flex: 1 }} edges={['bottom']}>
            <TouchableOpacity
              style={styles.modalOverlay}
              activeOpacity={1}
              onPress={e => {
                if (e.target === e.currentTarget) {
                  handleClose();
                }
              }}
            >
              <GestureDetector gesture={panGesture}>
                <Animated.View
                  style={[styles.bottomSheetContainer, animatedStyle]}
                >
                  {showHandle && (
                    <View style={styles.handleContainer}>
                      <View style={styles.handle} />
                    </View>
                  )}
                  {title && (
                    <View
                      style={[
                        styles.header,
                        !showDivider && styles.headerNoDivider,
                      ]}
                    >
                      <Text allowFontScaling={false} style={styles.headerTitle} numberOfLines={1}>
                        {title}
                      </Text>
                      {showCloseButton ? (
                        <TouchableOpacity
                          onPress={handleClose}
                          style={styles.closeButton}
                        >
                          <Text allowFontScaling={false} style={styles.closeButtonText}>✕</Text>
                        </TouchableOpacity>
                      ) : null}
                    </View>
                  )}
                  {children}
                </Animated.View>
              </GestureDetector>
            </TouchableOpacity>
          </Container>
        </GestureHandlerRootView>
      </KeyboardAvoidingView>
    </Modal>
  );
};

const styles = StyleSheet.create({
  modalOverlay: {
    flex: 1,
    backgroundColor: _COL.BLACK07,
    justifyContent: 'flex-end',
  },
  bottomSheetContainer: {
    backgroundColor: _COL.WHITE,
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
  },
  handleContainer: {
    alignItems: 'center',
  },
  handle: {
    width: 40,
    height: 4,
    marginTop: 12,
    backgroundColor: _COL.TEXT_GREY_LIGHT,
    borderRadius: 2,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginVertical: 26,
    borderBottomWidth: 1,
    borderBottomColor: _COL.BORDER,
  },
  headerNoDivider: {
    borderBottomWidth: 0,
  },
  headerTitle: {
    fontSize: 22,
    fontFamily: FONT.BOLD,
    color: _COL.FINAL_BLACK,
    flex: 1,
    textAlign: 'center',
  },
  closeButton: {
    paddingLeft: 4,
  },
  closeButtonPlaceholder: {
    width: 28,
    height: 28,
  },
  closeButtonText: {
    fontSize: 24,
    color: _COL.MAIN_BLACK,
  },
});

export default BottomActionSheet;
