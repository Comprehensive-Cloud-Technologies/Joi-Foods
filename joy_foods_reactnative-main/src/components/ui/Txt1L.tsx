import React, { FC, Key, ReactNode } from 'react';
import Animated, { FadeInRight } from 'react-native-reanimated';
import { Text, StyleProp, TextStyle } from 'react-native';

interface Props {
    txt?: string;
    sty?: StyleProp<TextStyle>;
    children?: ReactNode | ReactNode[] | string | number | null | undefined;
    animated?: boolean | undefined;
    keyy?: Key | null | undefined;
    onP?: () => void;
    nofLn?: number;
    selectable?: boolean;
    adjustToFit?: boolean;
};

const Txt1L: FC<Props> = ({ txt = "", sty, animated, children, keyy, onP, nofLn = 1, selectable, adjustToFit = false }) => {
    return (
        animated ? (
            <Animated.Text
                key={keyy}
                entering={FadeInRight}
                numberOfLines={1}
                adjustsFontSizeToFit
                allowFontScaling={false}
                style={[{ includeFontPadding: false }, sty]}
                onPress={onP}
                selectable={selectable}
            >
                {txt || children}
            </Animated.Text>
        ) : (
            <Text
                key={keyy}
                numberOfLines={nofLn}
                adjustsFontSizeToFit={adjustToFit}
                allowFontScaling={false}
                onPress={onP}
                selectable={selectable}
                style={[{ includeFontPadding: false }, sty]}
            >
                {txt || children}
            </Text>
        )
    )
}

export default Txt1L;