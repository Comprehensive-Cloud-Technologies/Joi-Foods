import React, {
  useState,
  useEffect,
  createContext,
  useContext,
  ReactNode,
  useRef,
} from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  Animated,
  StyleSheet,
  Keyboard,
  KeyboardEvent,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { _COL, _W, FONT, isIOS } from 'utils';
import { CLOSE_ICON } from 'assets';

type SnackbarType = 'error' | 'success' | 'warning' | 'info';

interface SnackbarState {
  visible: boolean;
  message: string;
  type: SnackbarType;
  duration?: number;
}

interface SnackbarContextType {
  showSnackbar: (
    message: string,
    type?: SnackbarType,
    duration?: number,
  ) => void;
  hideSnackbar: () => void;
}

interface SnackbarProviderProps {
  children: ReactNode;
}

const SnackbarContext = createContext<SnackbarContextType>({
  showSnackbar: () => { },
  hideSnackbar: () => { },
});

export const SnackbarProvider: React.FC<SnackbarProviderProps> = ({
  children,
}) => {
  const [snackbar, setSnackbar] = useState<SnackbarState>({
    visible: false,
    message: '',
    type: 'error',
  });
  const [keyboardHeight, setKeyboardHeight] = useState(0);

  const slideAnim = useRef(new Animated.Value(100)).current;

  const { bottom } = useSafeAreaInsets();

  const showSnackbar = (
    message: string,
    type: SnackbarType = 'error',
    duration: number = 3000,
  ) => {
    setSnackbar({ visible: true, message, type, duration });
  };

  const hideSnackbar = () => {
    Animated.timing(slideAnim, {
      toValue: 100,
      duration: 280,
      useNativeDriver: true,
    }).start(() => {
      setSnackbar(prev => ({ ...prev, visible: false }));
    });
  };

  useEffect(() => {
    const showSubscription = Keyboard.addListener(
      isIOS ? 'keyboardWillShow' : 'keyboardDidShow',
      (e: KeyboardEvent) => setKeyboardHeight(e.endCoordinates.height),
    );
    const hideSubscription = Keyboard.addListener(
      isIOS ? 'keyboardWillHide' : 'keyboardDidHide',
      () => setKeyboardHeight(0),
    );

    return () => {
      showSubscription.remove();
      hideSubscription.remove();
    };
  }, []);

  useEffect(() => {
    if (snackbar.visible) {
      Keyboard.dismiss();
      Animated.timing(slideAnim, {
        toValue: 0,
        duration: 280,
        useNativeDriver: true,
      }).start();

      const timer = setTimeout(hideSnackbar, snackbar.duration || 3000);

      return () => clearTimeout(timer);
    }
  }, [snackbar.visible]);

  return (
    <SnackbarContext.Provider value={{ showSnackbar, hideSnackbar }}>
      {children}
      {snackbar.visible && (
          <Animated.View
            style={[
              styles.snackbarContainer,
              {
                transform: [{ translateY: slideAnim }],
                bottom: isIOS ? (keyboardHeight || bottom) + 12 : bottom + 12,
              },
            ]}
          >
            <View
              style={[
                styles.snackbar,
                {
                  flexDirection: 'row',
                  backgroundColor: _COL.MAIN_BLACK,
                },
              ]}
            >
              <Text allowFontScaling={false} style={[styles.snackbarText]}>{snackbar.message}</Text>
              <TouchableOpacity
                onPress={hideSnackbar}
                hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}
              >
                <CLOSE_ICON />
              </TouchableOpacity>
            </View>
          </Animated.View>
      )}
    </SnackbarContext.Provider>
  );
};

export const useSnackbar = () => {
  const context = useContext(SnackbarContext);
  if (!context)
    throw new Error('useSnackbar must be used within SnackbarProvider');
  return context;
};

const styles = StyleSheet.create({
  snackbarContainer: {
    position: 'absolute',
    left: 0,
    right: 0,
    zIndex: 9999,
    paddingHorizontal: 16,
  },
  snackbar: {
    alignItems: 'center',
    borderRadius: 8,
    paddingHorizontal: 16,
    paddingVertical: 18,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
    elevation: 5,
  },
  iconContainer: {
    marginRight: 10,
  },
  snackbarText: {
    flex: 1,
    fontSize: 14,
    color: _COL.WHITE,
    fontFamily: FONT.SEMI_BOLD,
  },
});




// import React, { createContext, useContext, useState } from 'react';
// import { Snackbar } from 'react-native-paper';
// import { useSafeAreaInsets } from 'react-native-safe-area-context';
// import { _COL, FONT } from 'utils';

// type SnackbarType = 'error' | 'success' | 'info' | 'warning';

// interface SnackbarContextType {
//   showSnackbar: (message: string, type?: SnackbarType) => void;
// }

// const SnackbarContext = createContext<SnackbarContextType>({
//   showSnackbar: () => {},
// });

// export const SnackbarProvider: React.FC<{ children: React.ReactNode }> = ({
//   children,
// }) => {
//   const [visible, setVisible] = useState(false);
//   const [message, setMessage] = useState('');
//   const [type, setType] = useState<SnackbarType>('info');
//   const { bottom } = useSafeAreaInsets();

//   const showSnackbar = (msg: string, t: SnackbarType = 'info') => {
//     setMessage(msg);
//     setType(t);
//     setVisible(true);
//   };

//   return (
//     <SnackbarContext.Provider value={{ showSnackbar }}>
//       {children}

//       <Snackbar
//         visible={visible}
//         onDismiss={() => setVisible(false)}
//         duration={3000}
//         style={{
//           backgroundColor:
//             type === 'error'
//               ? _COL.ERROR
//               : type === 'success'
//               ? _COL.GREEN
//               : _COL.MAIN_BLACK,
//           marginBottom: bottom + 12,
//         }}
//         theme={{
//           colors: {
//             accent: _COL.WHITE,
//           },
//         }}
//         action={{
//           label: 'Close',
//           onPress: () => setVisible(false),
//           textColor: _COL.WHITE,
//         }}
//       >
//         {message}
//       </Snackbar>
//     </SnackbarContext.Provider>
//   );
// };

// export const useSnackbar = () => {
//   const context = useContext(SnackbarContext);
//   if (!context) {
//     throw new Error('useSnackbar must be used within SnackbarProvider');
//   }
//   return context;
// };
