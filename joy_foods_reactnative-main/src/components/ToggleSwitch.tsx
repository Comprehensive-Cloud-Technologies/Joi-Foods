import React, { useEffect } from 'react';
import { Pressable, StyleSheet, ViewStyle, StyleProp } from 'react-native';
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  withSpring,
  interpolateColor,
  ReduceMotion,
} from 'react-native-reanimated';

interface CustomToggleSwitchProps {
  value: boolean;
  onValueChange: (value: boolean) => void;
  disabled?: boolean;
  activeColor?: string;
  inactiveColor?: string;
  thumbColor?: string;
  style?: StyleProp<ViewStyle>;
}

const CustomToggleSwitch: React.FC<CustomToggleSwitchProps> = ({
  value,
  onValueChange,
  disabled = false,
  activeColor = '#34C759',
  inactiveColor = '#E5E7EB',
  thumbColor = '#FFFFFF',
  style,
}) => {
  const switchTranslate = useSharedValue(value ? 1 : 0);

  useEffect(() => {
    switchTranslate.value = withSpring(value ? 1 : 0, {
      damping: 150,
      stiffness: 950,
    });
  }, [value]);

  const handlePress = () => {
    if (!disabled) {
      onValueChange(!value);
    }
  };

  const animatedTrackStyle = useAnimatedStyle(() => {
    const backgroundColor = interpolateColor(
      switchTranslate.value,
      [0, 1],
      [inactiveColor, activeColor],
    );

    return {
      backgroundColor,
    };
  });

  const animatedThumbStyle = useAnimatedStyle(() => {
    const translateX = switchTranslate.value * 30;

    return {
      transform: [{ translateX }],
    };
  });

  return (
    <Pressable
      onPress={handlePress}
      disabled={disabled}
      style={[styles.container, style]}
      hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}
    >
      <Animated.View style={[styles.track, animatedTrackStyle]}>
        <Animated.View
          style={[
            styles.thumb,
            { backgroundColor: thumbColor },
            animatedThumbStyle,
          ]}
        />
      </Animated.View>
    </Pressable>
  );
};

export default CustomToggleSwitch;

const styles = StyleSheet.create({
  container: {
    justifyContent: 'center',
  },
  track: {
    width: 66,
    height: 36,
    borderRadius: 48,
    padding: 2,
    justifyContent: 'center',
  },
  thumb: {
    width: 32,
    height: 32,
    borderRadius: 16,
  },
});
