import { EYE_ICON } from 'assets';
import { FontS } from 'function';

import { langCodeT } from 'internationalization/context/types';
import React, {
  forwardRef,
  ReactNode,
  useImperativeHandle,
  useRef,
  useState,
} from 'react';
import {
  View,
  TextInput,
  Text,
  TouchableOpacity,
  ViewStyle,
  StyleProp,
  TextStyle,
  KeyboardTypeOptions,
  StyleSheet,
} from 'react-native';

import { _COL, FONT } from 'utils';

interface Props {
  value: string;
  onChangeText: (text: string) => void;
  error?: boolean;
  errorMessage?: string;
  label?: string;
  placeholder?: string;
  type?: string;
  showPasswordToggle?: boolean;
  icon?: ReactNode;
  editable?: boolean;
  multiline?: boolean;
  numberOfLines?: number;
  maxLength?: number;
  autoCapitalize?: 'none' | 'sentences' | 'words' | 'characters';
  keyboardType?: KeyboardTypeOptions;
  containerStyle?: ViewStyle;
  inputStyle?: StyleProp<TextStyle>;
  inputContainerStyle?: StyleProp<ViewStyle>;
  labelStyle?: StyleProp<TextStyle>;
  errorStyle?: StyleProp<TextStyle>;
  leftIcon?: ReactNode;
  rightIcon?: ReactNode;
  onLeftIconPress?: () => void;
  onRightIconPress?: () => void;
  onPressViewOnly?: () => void;
  viewOnly?: boolean;
  language?: langCodeT;
  isGradient?: boolean;
  bRadius?: number;
  lIcSty?: StyleProp<ViewStyle>;
  rightIconSty?: StyleProp<ViewStyle>;
}

export interface DynamicInputRef {
  setError: (hasError: boolean, message?: string) => void;
  clearError: () => void;
  focus: () => void;
  blur: () => void;
}

export const InputField = forwardRef<DynamicInputRef, Props>(
  (
    {
      value,
      onChangeText,
      label,
      placeholder = 'Enter text',
      type = 'text',
      showPasswordToggle = true,
      leftIcon,
      rightIcon,
      onLeftIconPress,
      onRightIconPress,
      onPressViewOnly,
      editable = true,
      multiline = false,
      numberOfLines = 1,
      maxLength,
      autoCapitalize,
      keyboardType,
      containerStyle,
      inputContainerStyle,
      inputStyle,
      labelStyle,
      errorStyle,
      language,
      viewOnly = false,
      isGradient = false,
      bRadius = 12,
      lIcSty,
      rightIconSty,
      ...textInputProps
    },
    ref,
  ) => {
    const [isPasswordVisible, setIsPasswordVisible] = useState(false);
    const [isFocused, setIsFocused] = useState(false);
    const [error, setError] = useState(false);
    const [errorMessage, setErrorMessage] = useState('');
    const textInputRef = useRef<TextInput>(null);

    const isRTL = language === 'ar';

    useImperativeHandle(ref, () => ({
      setError: (hasError, message = 'Please fill the input.') => {
        setError(hasError);
        setErrorMessage(message);
      },
      clearError: () => {
        setError(false);
        setErrorMessage('');
      },
      focus: () => textInputRef.current?.focus(),
      blur: () => textInputRef.current?.blur(),
    }));

    const styles = StyleSheet.create({
      container: {},
      label: {
        fontFamily: FONT.MEDIUM,
        fontSize: FontS(14),
        lineHeight: FontS(22),
        color: _COL.FINAL_BLACK,
        paddingBottom: FontS(2),
        paddingLeft: FontS(2),
      },
      inputContainer: {
        flexDirection: 'row',
        alignItems: 'center',
        borderWidth: 1.5,
        borderColor: _COL.BORDER,
        borderRadius: bRadius,
        backgroundColor: _COL.WHITE,
        overflow: 'hidden',
        marginTop: 4
      },
      inputContainerError: { borderColor: _COL.ERROR },
      inputContainerFocused: {
        borderColor: _COL.FINAL_BLACK,
      },
      inputContainerDisabled: {
        backgroundColor: "#F8F7F7",
        borderColor: 'transparent',
      },
      input: {
        flex: 1,
        color: _COL.BLACK,
        padding: FontS(3),
        fontSize: FontS(14),
        fontFamily: FONT.REGULAR,
        includeFontPadding: false,
        paddingVertical: 14,
      },
      inputMultiline: { minHeight: 100, textAlignVertical: 'top' },
      errorText: {
        fontSize: 12,
        color: _COL.ERROR,
        marginTop: 4,
        fontFamily: FONT.SEMI_BOLD,
      },
      rightBtnSty: { paddingHorizontal: 16 },
    });

    const isSecureField = type === 'password' && !isPasswordVisible;

    const labelRTLStyle = isRTL
      ? { paddingLeft: 0, paddingRight: FontS(2), textAlign: 'right' as const }
      : {};
    const inputContainerRTLStyle = isRTL
      ? { flexDirection: 'row-reverse' as const }
      : {};
    const inputRTLStyle = isRTL
      ? { textAlign: 'right' as const, writingDirection: 'rtl' as const }
      : {};
    const errorRTLStyle = isRTL ? { textAlign: 'right' as const } : {};

    const renderLeftIcon = () => {
      if (!leftIcon) return null;
      const IconWrapper = onLeftIconPress ? TouchableOpacity : View;
      return (
        <IconWrapper
          onPress={onLeftIconPress}
          style={[{ paddingHorizontal: 12 }, lIcSty]}
          activeOpacity={0.7}
        >
          {leftIcon}
        </IconWrapper>
      );
    };

    const renderRightIcon = () => {
      if (type === 'password' && showPasswordToggle) {
        return (
          <TouchableOpacity
            onPress={() => setIsPasswordVisible(!isPasswordVisible)}
            style={[styles.rightBtnSty]} //{ opacity: isFocused ? 1 : 0.3 }
            activeOpacity={0.8}
          >
            <EYE_ICON isOpen={isPasswordVisible} />
          </TouchableOpacity>
        );
      }
      if (!rightIcon) return null;
      const IconWrapper = onRightIconPress ? TouchableOpacity : View;
      return (
        <IconWrapper
          onPress={onRightIconPress}
          style={[{ paddingHorizontal: 12 }, rightIconSty]}
          activeOpacity={0.8}
        >
          {rightIcon}
        </IconWrapper>
      );
    };

    const renderInputContent = () => {
      const content = (
        <View
          style={[
            styles.inputContainer,
            inputContainerRTLStyle,
            isFocused && styles.inputContainerFocused,
            !editable && styles.inputContainerDisabled,
            error && styles.inputContainerError,
            inputContainerStyle,
          ]}
        >
          {renderLeftIcon()}
          <TextInput
            ref={textInputRef}
            style={[
              styles.input,
              inputRTLStyle,
              multiline && styles.inputMultiline,
              inputStyle,
            ]}
            allowFontScaling={false}
            value={value}
            autoCapitalize={autoCapitalize}
            onChangeText={onChangeText}
            placeholder={placeholder}
            placeholderTextColor={_COL.TEXT_BLACK_DARK + '55'}
            secureTextEntry={isSecureField}
            onFocus={() => setIsFocused(true)}
            onBlur={() => setIsFocused(false)}
            editable={!viewOnly && editable}
            multiline={multiline}
            numberOfLines={numberOfLines}
            maxLength={maxLength}
            keyboardType={keyboardType}
            pointerEvents={viewOnly ? 'none' : 'auto'}
            {...textInputProps}
          />
          {renderRightIcon()}
        </View>
      );

      if (viewOnly) {
        return (
          <TouchableOpacity
            activeOpacity={0.8}
            onPress={onPressViewOnly}
          // style={{ flex: 1 }}
          >
            {content}
          </TouchableOpacity>
        );
      }

      return content;
    };

    return (
      <View style={[styles.container, containerStyle]}>
        {label && (
          <Text allowFontScaling={false} style={[styles.label, labelRTLStyle, labelStyle]}>{label}</Text>
        )}

        {isGradient ? (
          <View
            style={{
              borderRadius: bRadius,
              overflow: 'hidden',
              elevation: 10,
              shadowColor: '#455785',
            }}
          >
            {renderInputContent()}
          </View>
        ) : (
          renderInputContent()
        )}

        {error && errorMessage && (
          <Text allowFontScaling={false} style={[styles.errorText, errorRTLStyle, errorStyle]}>
            {errorMessage}
          </Text>
        )}
      </View>
    );
  },
);

export default InputField;
