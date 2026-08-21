import { StyleProp, StyleSheet, Text, TouchableOpacity, View, ViewStyle } from 'react-native'
import React from 'react'
import { _COL, FONT } from 'utils/constants'
import { useT } from 'internationalization';


const TextButton = ({ onP, style, isUnderline, text }: { onP: () => void, style?: StyleProp<ViewStyle>, isUnderline?: boolean, text: string }) => {
    const { t } = useT();
    return (
        <TouchableOpacity
            onPress={onP}
            style={[{ alignSelf: 'center' }, style]}
        >
            <Text allowFontScaling={false} style={[styles.cancelBtnText, isUnderline && { textDecorationLine: 'underline', textDecorationStyle: 'solid' }]}>
                {text}
            </Text>
        </TouchableOpacity>
    )
}

export default TextButton

const styles = StyleSheet.create({
    cancelBtnText: {
        fontSize: 14,
        fontFamily: FONT.SEMI_BOLD,
        color: _COL.FINAL_BLACK,
        textAlign: 'center',
        paddingVertical: 16,
        paddingHorizontal: 26,
    },

})