import React, {
  forwardRef,
  useCallback,
  useEffect,
  useImperativeHandle,
  useMemo,
  useRef,
  useState,
} from 'react';
import {
  type KeyboardTypeOptions,
  type NativeSyntheticEvent,
  type TextInputKeyPressEventData,
  View,
  ViewStyle,
  StyleProp,
  TextStyle,
  ReturnKeyType,
  ColorValue,
  TextInput,
} from 'react-native';
import { FONT, _COL, _W, isIOS } from 'utils';
import { OTPStyles } from './styles';
import { FontS } from 'function';

interface OTPTextInputProps {
  defaultValue?: string;
  inputCount?: number;
  tintColor?: string;
  offTintColor?: string;
  inputMaxLength?: number;
  onTextChangeHandler?: (value: string) => void;
  onFinalHandler?: () => void;
  containerStyle?: StyleProp<ViewStyle>;
  textInputStyle?: StyleProp<TextStyle>;
  keyboardType?: KeyboardTypeOptions;
  ref?: React.Ref<OTPTextInputHandle>;
  editable?: boolean;
  onFocus?: (index: number) => void;
  onBlur?: (index: number) => void;
  autoFocus?: boolean;
  useNumbersRegex?: boolean;
  useLettersRegex?: boolean;
  useCustomRegex?: boolean;
  skipValidation?: boolean;
  customRegex?: RegExp;
  secureTextEntry?: boolean;
  defValIdx?: { idx: number; val: string }[];
  onSubE?: () => void;
  rKeyT?: ReturnKeyType;
  txtCol?: ColorValue;
}

export interface OTPTextInputHandle {
  clear: () => void;
  setValue: (value: string) => void;
  onFcs: (val: number) => void;
}

const OTPTextInput = forwardRef<OTPTextInputHandle, OTPTextInputProps>(
  (
    {
      defaultValue = '',
      inputCount = 4,
      tintColor = '#566193',
      offTintColor = '#DADADA',
      inputMaxLength = 1,
      containerStyle = {},
      textInputStyle = {},
      onTextChangeHandler = () => { },
      onFinalHandler = () => { },
      onBlur = () => { },
      onFocus = () => { },
      keyboardType = 'numeric',
      editable = true,
      autoFocus = false,
      useNumbersRegex = true,
      useCustomRegex = false,
      skipValidation = false,
      customRegex = undefined,
      secureTextEntry = false,
      defValIdx = [],
      onSubE = () => { },
      rKeyT,
      txtCol,
      useLettersRegex,
    },
    ref,
  ) => {
    if (useNumbersRegex && useCustomRegex) {
      throw new Error(
        'You cannot set both useNumbersRegex and useCustomRegex to true!',
      );
    }

    const regex: RegExp = useMemo(() => {
      if (useNumbersRegex) {
        return new RegExp('\\d*');
      } else if (useCustomRegex && customRegex) {
        return customRegex;
      }
      return new RegExp('.*');
    }, [useNumbersRegex, useCustomRegex, customRegex]);

    const [focusedInput, setFocusedInput] = useState(autoFocus ? 0 : -1);
    const [otpText, setOtpText] = useState<string[]>(
      Array.from({ length: inputCount }, (_, i) => defaultValue[i] || ''),
    );

    const inputsRef = useRef<Array<React.RefObject<TextInput | null>>>(
      Array.from({ length: inputCount }, () => React.createRef<TextInput>()),
    );

    const clear = () => {
      setOtpText(new Array(inputCount).fill(''));
      setFocusedInput(0);
      inputsRef.current[0]?.current?.focus();
    };

    const setValue = (value: string): void => {
      const newOtp = Array.from(
        { length: inputCount },
        (_, i) => value[i] || '',
      );
      setOtpText(newOtp);
      const lastFilledIndex = Math.min(value.length, inputCount - 1);
      setFocusedInput(lastFilledIndex);
    };

    useImperativeHandle(ref, () => ({
      clear,
      setValue,
      onFcs: fc => {
        setFocusedInput(fc);
        inputsRef.current[fc]?.current?.focus();
      },
    }));

    useEffect(() => {
      if (focusedInput >= 0 && focusedInput < inputCount) {
        inputsRef.current[focusedInput]?.current?.focus();
      }
    }, [focusedInput, inputCount]);

    const debounceOnTextChangeHandler = useMemo(() => {
      let timeout: ReturnType<typeof setTimeout> | null = null;
      return (text: string) => {
        if (timeout !== null) clearTimeout(timeout);
        timeout = setTimeout(() => {
          onTextChangeHandler(text);
          timeout = null;
        }, 125);
      };
    }, [onTextChangeHandler]);

    const updateOTP = useCallback(
      (newOtp: string[]) => {
        setOtpText(newOtp);
        debounceOnTextChangeHandler(newOtp.join(''));
      },
      [debounceOnTextChangeHandler],
    );

    // Helper to check if index is disabled
    const isDisabledIndex = useCallback(
      (index: number) => {
        return defValIdx.some(item => item.idx === index);
      },
      [defValIdx],
    );

    // Find next available (non-disabled) index
    const getNextAvailableIndex = useCallback(
      (currentIndex: number) => {
        for (let i = currentIndex + 1; i < inputCount; i++) {
          if (!isDisabledIndex(i)) return i;
        }
        return currentIndex;
      },
      [inputCount, isDisabledIndex],
    );

    // Find previous available (non-disabled) index
    const getPreviousAvailableIndex = useCallback(
      (currentIndex: number) => {
        for (let i = currentIndex - 1; i >= 0; i--) {
          if (!isDisabledIndex(i)) return i;
        }
        return 0;
      },
      [isDisabledIndex],
    );

    const onTextChange = useCallback(
      (text: string, position: number): void => {
        if (!skipValidation && !regex.test(text)) {
          return;
        }

        const newOtp = [...otpText];

        if (text.length === 0) {
          // Handle clearing current field
          newOtp[position] = '';
          updateOTP(newOtp);
          return;
        }

        if (text.length === 1) {
          // Single character input
          newOtp[position] = text;
          updateOTP(newOtp);

          // Move to next available input
          if (position < inputCount - 1) {
            const nextIndex = getNextAvailableIndex(position);
            setFocusedInput(nextIndex);
          } else {
            // Last input filled - trigger final handler
            onFinalHandler();
          }
        } else {
          // Handle paste - multiple characters
          let currentPos = position;
          for (let i = 0; i < text.length && currentPos < inputCount; i++) {
            if (!isDisabledIndex(currentPos)) {
              newOtp[currentPos] = text[i];
            }
            currentPos++;
          }
          updateOTP(newOtp);

          // Focus on last filled position or next empty
          const nextEmptyIndex = newOtp.findIndex(
            (val, idx) => idx > position && val === '' && !isDisabledIndex(idx),
          );

          if (nextEmptyIndex !== -1) {
            setFocusedInput(nextEmptyIndex);
          } else {
            setFocusedInput(Math.min(currentPos, inputCount - 1));
          }
        }
      },
      [
        regex,
        otpText,
        inputCount,
        updateOTP,
        isDisabledIndex,
        getNextAvailableIndex,
        onFinalHandler,
      ],
    );

    const onKeyPress = useCallback(
      (
        event: NativeSyntheticEvent<TextInputKeyPressEventData>,
        position: number,
      ) => {
        const {
          nativeEvent: { key },
        } = event;

        if (key === 'Backspace') {
          if (otpText[position] === '') {
            if (position > 0) {
              const prevIndex = getPreviousAvailableIndex(position);
              const newOtp = [...otpText];
              newOtp[prevIndex] = '';
              updateOTP(newOtp);
              setFocusedInput(prevIndex);
            }
          }
        }
      },
      [otpText, updateOTP, getPreviousAvailableIndex],
    );

    const generateInputs = useCallback(
      () =>
        Array.from({ length: inputCount }, (_, i) => {
          const defObj = defValIdx.find(obj => obj.idx === i);
          const isDisabled = !!defObj;

          const inputStyle: StyleProp<TextStyle> = [
            OTPStyles.textInput,
            textInputStyle,
            {
              borderColor: i === focusedInput ? tintColor : offTintColor,
              fontFamily: isDisabled ? FONT.BLACK : FONT.SEMI_BOLD,
              fontSize: secureTextEntry
                ? FontS(16)
                : isDisabled
                  ? FontS((_W * 0.35) / inputCount)
                  : FontS(18),
              includeFontPadding: false,
              paddingHorizontal: 0,
              textAlign: 'center',
              flex: 1,
            },
          ];

          return (
            <TextInput
              key={`OTPTextInput_${i}`}
              disableFullscreenUI
              spellCheck={false}
              autoCorrect={false}
              blurOnSubmit={false}
              clearButtonMode="never"
              autoCapitalize="characters"
              underlineColorAndroid="transparent"
              ref={inputsRef.current[i]}
              autoFocus={autoFocus && i === 0}
              keyboardType={keyboardType}
              style={inputStyle}
              selectionColor={_COL.BLUE}
              value={
                isDisabled
                  ? defObj.val
                  : otpText[i] && secureTextEntry
                    ? '✱'
                    : otpText[i] || ''
              }
              maxLength={inputMaxLength}
              cursorColor={_COL.BLACK}
              selectTextOnFocus
              autoComplete={isIOS ? 'one-time-code' : 'sms-otp'}
              onFocus={() => {
                onFocus(i);
                setFocusedInput(i);
              }}
              onBlur={() => {
                onBlur(i);
              }}
              onChangeText={text => onTextChange(text, i)}
              onKeyPress={event => onKeyPress(event, i)}
              editable={!isDisabled && editable}
              allowFontScaling={false}
              maxFontSizeMultiplier={isDisabled ? 1 : 0.5}
              onSubmitEditing={() => {
                if (i === inputCount - 1) {
                  onSubE();
                } else {
                  const nextIndex = getNextAvailableIndex(i);
                  setFocusedInput(nextIndex);
                }
              }}
              returnKeyType={i === inputCount - 1 ? rKeyT || 'done' : 'next'}
            />
          );
        }),
      [
        inputCount,
        textInputStyle,
        focusedInput,
        tintColor,
        offTintColor,
        autoFocus,
        keyboardType,
        otpText,
        inputMaxLength,
        editable,
        onFocus,
        onBlur,
        onTextChange,
        onKeyPress,
        secureTextEntry,
        txtCol,
        defValIdx,
        onSubE,
        rKeyT,
        getNextAvailableIndex,
      ],
    );

    return (
      <View style={[OTPStyles.container, containerStyle]}>
        {generateInputs()}
      </View>
    );
  },
);

export default OTPTextInput;
