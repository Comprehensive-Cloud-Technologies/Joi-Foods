import React, { FC, Fragment, JSX, ReactNode, useCallback, memo } from 'react';
import { Text, Pressable, StyleProp, ViewStyle, TextStyle, ColorValue, ActivityIndicator, View, DimensionValue, GestureResponderEvent, StyleSheet, TouchableOpacity } from 'react-native';
import Animated, { BaseAnimationBuilder, Easing, EntryExitAnimationFunction, FadeIn, FadeOut, useAnimatedStyle, useSharedValue, withTiming } from 'react-native-reanimated';
import { FONT, _COL, _WIDTH, isIOS } from 'utils';
import { FontS } from 'function';
import Txt1L from './Txt1L';

interface Props {
  title: string;
  onP?: ((event: GestureResponderEvent) => void) & (() => void);
  rounded?: boolean;
  vSty?: StyleProp<ViewStyle>;
  avSTy?: StyleProp<ViewStyle>;
  tvSty?: StyleProp<ViewStyle>;
  tSty?: StyleProp<TextStyle>;
  btnSty?: StyleProp<ViewStyle>;
  bgCol?: ColorValue;
  tCol?: ColorValue;
  bordered?: boolean;
  borderCol?: ColorValue;
  borderW?: number;
  borderR?: number;
  mTop?: DimensionValue;
  mBottom?: DimensionValue;
  mLeft?: DimensionValue;
  mRight?: DimensionValue;
  mVertical?: DimensionValue;
  mHorizontal?: DimensionValue;
  isLoading?: boolean;
  autoSize?: boolean;
  width?: DimensionValue;
  lIc?: JSX.Element | ReactNode;
  rIc?: JSX.Element | ReactNode;
  isDisabled?: boolean;
  entering?:
  | BaseAnimationBuilder
  | typeof BaseAnimationBuilder
  | EntryExitAnimationFunction;
  touchable?: boolean;
  isGesture?: boolean;
  secondary?: boolean;
  noShadow?: boolean;
  children?: ReactNode | null;
  ctr?: boolean;
  pV?: number;
  pT?: number;
  pB?: number;
  accessibilityLabel?: string;
  accessible?: boolean;
}

const BTN: FC<Props> = ({
  title, onP, rounded = true, isLoading, isGesture, vSty, avSTy, tSty, btnSty, entering, secondary, bgCol, tCol, autoSize, width, bordered, borderW,
  borderCol, borderR, tvSty, isDisabled = false, mTop, mBottom, lIc, rIc, mVertical, touchable, noShadow, children, mHorizontal, ctr, pV, pB = 0,
  pT = 0, mLeft, mRight, accessibilityLabel, accessible,
}) => {
  const s_V = useSharedValue(1);
  const aStyles = useAnimatedStyle(() => ({ transform: [{ scale: s_V.value }] }));
  const isTouchable = touchable
    ? true
    : isGesture
      ? isIOS
        ? false
        : true
      : false;
  const shadow = noShadow ? {} : {};

  const styles = StyleSheet.create({
    BtnV: {
      width: '100%',
      justifyContent: 'center',
      overflow: 'hidden',
    },
    BtnVr: {
      width: '100%',
      justifyContent: 'center',
      borderRadius: FontS(8),
      overflow: 'hidden',
    },
    Btn: {
      padding: FontS(15),
      paddingTop: FontS(14.5),
      paddingBottom: FontS(15.5),
      width: '100%',
      flexDirection: 'row',
      alignItems: 'center',
      justifyContent: 'center',
    },
    BtnTxt: {
      fontFamily: FONT.BOLD,
      fontSize: FontS(14),
      color: _COL.WHITE,
      lineHeight: FontS(24),
      textAlign: 'center',
      width: _WIDTH * 0.8,
      maxWidth: '100%',
      alignSelf: 'center',
    },
    lIcV: {
      top: 0,
      bottom: 0,
      left: _WIDTH * 0.04,
      position: 'absolute',
      alignItems: 'center',
      justifyContent: 'center',
    },
    rIcV: {
      top: 0,
      bottom: 0,
      right: _WIDTH * 0.04,
      position: 'absolute',
      alignItems: 'center',
      justifyContent: 'center',
    },
  });

  const Content = useCallback(() => {
    return (
      <Fragment>
        <View style={styles.lIcV}>{lIc}</View>
        <Animated.View
          style={tvSty}
          key={isLoading ? 'ActivityIndicator' : 'titleText'}
          entering={FadeIn}
          exiting={FadeOut.duration(isLoading ? 70 : 100)}
        >
          {isLoading ? (
            <>
              <ActivityIndicator
                size={_WIDTH * 0.05}
                color={tCol || (secondary ? _COL.SECONDARY : _COL.PRIMARY)}
                style={StyleSheet.absoluteFillObject}
              />
            </>
          ) : null}
          {autoSize ? (
            <Txt1L
              sty={[styles.BtnTxt, tSty, {
                color: tCol || (bordered ? _COL.PRIMARY : _COL.WHITE),
                opacity: isLoading ? 0 : 1,
              }]}
            >
              {title}
            </Txt1L>
          ) : (
            <Text
              style={[styles.BtnTxt, tSty, {
                color: tCol || (bordered ? _COL.PRIMARY : _COL.WHITE),
                opacity: isLoading ? 0 : 1,
                lineHeight: FontS(24),
                height: FontS(24),
              }]}
              allowFontScaling={false}
            >
              {title}
            </Text>
          )}
        </Animated.View>
        <View style={styles.rIcV}>{rIc}</View>
      </Fragment>
    );
  }, [isLoading, autoSize, title, tCol, lIc, rIc, tvSty, tSty, bordered, secondary]);

  const border_R = rounded ? borderR ?? FontS(10) : borderR || 0;

  return (
    <Animated.View
      entering={entering}
      style={[aStyles, shadow, avSTy, {
        marginTop: mTop,
        marginBottom: mBottom,
        width: width || '100%',
        left: mLeft,
        right: mRight,
        marginVertical: mVertical,
        marginHorizontal: mHorizontal,
        borderWidth: bordered ? borderW || 1 : 1,
        borderRadius: bordered ? borderR || border_R : border_R,
        borderColor: bordered
          ? borderCol || _COL.BORDER
          : _COL.TRANSPARENT,
        backgroundColor:
          bgCol ||
          (isDisabled
            ? _COL.GREY
            : _COL.PRIMARY_RED),
        alignSelf: ctr ? 'center' : undefined,
      }]}
    >
      <View style={[rounded ? styles.BtnVr : styles.BtnV, vSty]}>
        {isTouchable ? (
          <TouchableOpacity
            onPress={onP}
            activeOpacity={0.9}
            disabled={isLoading || isDisabled}
            style={[styles.Btn, btnSty, {
              minWidth: '100%',
              paddingVertical: pV,
              paddingTop: FontS(12 + pT),
              paddingBottom: FontS(12 + pB),
            }]}
            onPressOut={() =>
              (s_V.value = withTiming(1, { duration: 75, easing: Easing.ease }))
            }
            onPressIn={() =>
            (s_V.value = withTiming(0.975, {
              duration: 101,
              easing: Easing.ease,
            }))
            }
            accessibilityLabel={accessibilityLabel || title}
            accessible={accessible}
          >
            {Content()}
          </TouchableOpacity>
        ) : (
          <Pressable
            style={({ pressed }) => {
              s_V.value = withTiming(pressed ? 0.975 : 1, {
                duration: pressed ? 101 : 75,
                easing: Easing.linear,
              });
              return [styles.Btn, btnSty, {
                paddingVertical: pV,
                paddingTop: FontS(12 + pT),
                paddingBottom: FontS(12 + pB),
              }];
            }}
            onPress={onP}
            disabled={isLoading || isDisabled}
          >
            {Content()}
          </Pressable>
        )}
        {children}
      </View>
    </Animated.View>
  );
};

export default memo(BTN);
