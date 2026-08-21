import React, { useCallback, useState, useEffect } from 'react';
import { View, StyleSheet, TouchableOpacity, Text, StatusBar, Linking } from 'react-native';
import { Camera, useCameraDevice, useCodeScanner, useCameraPermission } from 'react-native-vision-camera';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { ANIMATED_SCAN_QR, CLOSE_LARGE_IC } from 'assets';
import { _COL, _WIDTH, FONT } from 'utils';
import { StackProps } from 'types';
import { useT } from 'internationalization';
import useValidateStoreHook from 'hooks/useValidateStoreHook';

const ScanQRController = ({ navigation, route }: StackProps<'ScanQR' | "ScanQRCode">) => {
  const { t } = useT();
  const insets = useSafeAreaInsets();
  const device = useCameraDevice('back');
  const { hasPermission, requestPermission } = useCameraPermission();
  const { validateStore } = useValidateStoreHook();

  const params = route.params;
  const [isActive, setIsActive] = useState(false);
  const [isScanning, setIsScanning] = useState(false);
  const [isPermissionGranted, setIsPermissionGranted] = useState(false);

  // Handle permission on mount
  useEffect(() => {
    const checkPermission = async () => {
      if (!hasPermission) {
        const granted = await requestPermission();
        setIsPermissionGranted(granted);
        setIsActive(granted);
      } else {
        setIsPermissionGranted(true);
        setIsActive(true);
      }
    };
    checkPermission();

    return () => {
      setIsActive(false);
    };
  }, [hasPermission, requestPermission]);


  const codeScanner = useCodeScanner({
    codeTypes: ['qr'],
    onCodeScanned: async (codes) => {
      if (isScanning || codes.length === 0) return;

      const scannedValue = codes[0].value;
      if (scannedValue) {
        setIsScanning(true);
        setIsActive(false);

        const { success } = await validateStore(scannedValue);
        if (success) {
          if (params?.isFromStoreChange) {
            navigation.reset({
              index: 0,
              routes: [
                { name: 'AppNavigator', state: { routes: [{ name: 'Menu' }], }, }
              ],
            });

          }
        } else {
          setTimeout(() => {
            setIsScanning(false);
            setIsActive(true);
          }, 2000);
        }
      }
    },
  });

  if (!isPermissionGranted && hasPermission === false) {
    return (
      <View style={styles.container}>
        <Text style={styles.errorText}>{t('CAMERA_PERMISSION_DENIED_MSG')}</Text>
        <TouchableOpacity
          style={styles.retryBtn}
          onPress={() => Linking.openSettings()}
        >
          <Text style={styles.retryText}>{t('PLEASE_ALLOW_CAMERA_PERMISSION_FROM_SETTINGS')}</Text>
        </TouchableOpacity>
      </View>
    );
  }

  if (device == null) {
    return (
      <View style={styles.container}>
        <Text style={styles.errorText}>{t('CAMERA_NOT_FOUND')}</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="transparent" translucent />
      <Camera
        style={StyleSheet.absoluteFill}
        device={device}
        isActive={isActive}
        codeScanner={codeScanner}
        onError={(e) => console.log('Camera Error:', e)}
        photo={false}
        video={false}
      />

      {/* Overlay */}
      <View style={[styles.overlay, { paddingTop: insets.top + 20 }]}>
        <TouchableOpacity
          style={styles.closeBtn}
          onPress={() => navigation.goBack()}
        >
          <CLOSE_LARGE_IC />
        </TouchableOpacity>

        <View style={styles.scanAreaContainer}>
          <ANIMATED_SCAN_QR isAnimation />
        </View>
        <Text style={styles.hintText}>{t('PLACE_QR_CODE_INSIDE_FRAME')}</Text>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.MAIN_BLACK,
    justifyContent: 'center',
    alignItems: 'center',
  },
  overlay: {
    ...StyleSheet.absoluteFillObject,
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingBottom: 40,
  },
  closeBtn: {
    alignSelf: 'flex-start',
    marginLeft: 20,
    backgroundColor: _COL.WHITE06,
    padding: 10,
    borderRadius: 50,
  },
  scanAreaContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  scanFrame: {
    width: _WIDTH * 0.7,
    height: _WIDTH * 0.7,
    borderWidth: 2,
    borderColor: _COL.WHITE,
    borderRadius: 20,
    backgroundColor: 'transparent',
  },
  hintText: {
    fontSize: 16,
    fontFamily: FONT.MEDIUM,
    color: _COL.WHITE,
    textAlign: 'center',
  },
  errorText: {
    color: _COL.WHITE,
    fontSize: 18,
    textAlign: 'center',
    paddingHorizontal: 20,
  },
  retryBtn: {
    marginTop: 20,
    backgroundColor: _COL.WHITE,
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: 10,
  },
  retryText: {
    color: _COL.MAIN_BLACK,
    fontSize: 16,
    fontFamily: FONT.BOLD,
  },
});

export default ScanQRController;

