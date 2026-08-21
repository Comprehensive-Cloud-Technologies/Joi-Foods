import React, { useEffect, useRef } from 'react';
import { View, Text, StyleSheet, Animated } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { _COL, FONT } from 'utils';

const NoInternet = () => {
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const slideAnim = useRef(new Animated.Value(30)).current;
  // const recheckConnection = async () => {
  //   const state = await NetInfo.fetch();
  //   console.log("STATE::", state);
  //   const connected = state.isConnected === true;
  //   dispatch({ type: SET_IS_CONNECTED, isConnected: connected });
  // };

  useEffect(() => {
    Animated.parallel([
      Animated.timing(fadeAnim, {
        toValue: 1,
        duration: 300,
        useNativeDriver: true,
      }),
      Animated.timing(slideAnim, {
        toValue: 0,
        duration: 300,
        useNativeDriver: true,
      }),
    ]).start();
  }, []);

  return (
    <SafeAreaView style={styles.container}>
      <Animated.View
        style={[
          styles.content,
          { opacity: fadeAnim, transform: [{ translateY: slideAnim }] },
        ]}
      >
        <View style={styles.blobWrapper}>
          <View style={styles.blobOuter} />
          <View style={styles.blobInner} />

          <View style={styles.clocheContainer}>
            <Text allowFontScaling={false} style={styles.clocheEmoji}>🍽️</Text>
            <View style={styles.xOverlay} pointerEvents="none">
              <View style={[styles.xLine, styles.xLine1]} />
              <View style={[styles.xLine, styles.xLine2]} />
            </View>
          </View>
        </View>

        <Text allowFontScaling={false} style={styles.title}>No internet{'\n'}connection.</Text>

        <Text allowFontScaling={false} style={styles.message}>
          Looks like your meal request can't go through right now. Check your
          connection and try again.
        </Text>

        {/* <BTN title='Try Again' mTop={32} /> */}

      </Animated.View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  content: {
    flex: 1,
    paddingHorizontal: 28,
    justifyContent: 'center'
  },
  blobWrapper: {
    alignItems: 'center',
    justifyContent: 'center',
    height: 260,
  },
  blobOuter: {
    position: 'absolute',
    width: 280,
    height: 240,
    borderRadius: 140,
    backgroundColor: '#FFF5D6',
  },
  blobInner: {
    position: 'absolute',
    width: 200,
    height: 180,
    borderRadius: 100,
    backgroundColor: '#FFEEBA',
    opacity: 0.6,
  },
  clocheContainer: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  clocheEmoji: {
    fontSize: 100,
  },
  xOverlay: {
    position: 'absolute',
    width: 110,
    height: 110,
    alignItems: 'center',
    justifyContent: 'center',
  },
  xLine: {
    position: 'absolute',
    width: 100,
    height: 13,
    borderRadius: 7,
    backgroundColor: '#E8552A',
  },
  xLine1: {
    transform: [{ rotate: '45deg' }],
  },
  xLine2: {
    transform: [{ rotate: '-45deg' }],
  },
  title: {
    fontSize: 30,
    fontFamily: FONT.BOLD,
    color: _COL.FINAL_BLACK,
    lineHeight: 38,
    textAlign: "center",
    marginTop: 22
  },
  message: {
    fontSize: 15,
    color: '#888888',
    lineHeight: 23,
    marginTop: 16
  },
});

export default NoInternet;