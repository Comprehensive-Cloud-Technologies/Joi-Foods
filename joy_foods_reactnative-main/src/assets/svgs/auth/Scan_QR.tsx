import React, { useEffect } from 'react';
import { View, StyleSheet } from 'react-native';
import Svg, { Path } from 'react-native-svg';
import Animated, { useSharedValue, useAnimatedStyle, withTiming, withRepeat } from 'react-native-reanimated';

const AnimatedPath = Animated.createAnimatedComponent(Path);

interface AnimatedLineProps {
    isAnimation: boolean;
}

const AnimatedLine: React.FC<AnimatedLineProps> = ({ isAnimation }) => {
    const translateY = useSharedValue(-100);

    useEffect(() => {
        if (isAnimation) {
            translateY.value = withRepeat(withTiming(100, { duration: 2500 }), -1, true);
        }
    }, [isAnimation]);

    const animatedStyle = useAnimatedStyle(() => {
        return {
            transform: [{ translateY: translateY.value }],
        };
    });

    return (
        <View style={styles.container}>
            <Svg width="274" height="240" viewBox="0 0 274 240" fill="none">
                <Path d="M40 10H22C15.3726 10 10 15.3726 10 22V40" stroke="white" strokeWidth="6" strokeLinecap="round" strokeLinejoin="round" />
                <Path d="M234 10H252C258.627 10 264 15.3726 264 22V40" stroke="white" strokeWidth="6" strokeLinecap="round" strokeLinejoin="round" />
                <Path d="M234 230H252C258.627 230 264 224.627 264 218V200" stroke="white" strokeWidth="6" strokeLinecap="round" strokeLinejoin="round" />
                <Path d="M40 230H22C15.3726 230 10 224.627 10 218V200" stroke="white" strokeWidth="6" strokeLinecap="round" strokeLinejoin="round" />

                {isAnimation ? (
                    <AnimatedPath
                        d="M15 120H259"
                        stroke="#FFCC66"
                        strokeWidth="4"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        /* @ts-ignore */
                        style={animatedStyle}
                    />
                ) : (
                    <Path
                        d="M15 120H259"
                        stroke="#FFCC66"
                        strokeWidth="4"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                    />
                )}
            </Svg>
        </View>
    );
};

const styles = StyleSheet.create({
    container: {
        width: 274,
        height: 240,
        justifyContent: 'center',
        alignItems: 'center',
    },
});

export default AnimatedLine;
