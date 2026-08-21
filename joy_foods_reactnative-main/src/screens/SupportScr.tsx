import React, { useState, useRef, useEffect } from 'react';
import {
  FlatList,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
  Animated,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { _COL, FONT, isIOS } from 'utils';
import { BACK_BTN_IC, DOWN_IC } from 'assets';
import { useT } from 'internationalization';
import { BTN, DynamicInputRef, InputField, useLoader, useSnackbar } from 'components';
import { StackProps } from 'types';
import { GETreq, POSTreq } from 'api';

const SupportScr = ({ navigation, route }: StackProps<'SupportScr'>) => {

  const routeParams = route.params || {};
  const insets = useSafeAreaInsets();
  const { t } = useT();
  const [topic, setTopic] = useState('');
  const [subject, setSubject] = useState('');
  const [message, setMessage] = useState('');
  const [topicError, setTopicError] = useState('');
  const subjectRef = useRef<DynamicInputRef>(null);
  const messageRef = useRef<DynamicInputRef>(null);
  const [isTopic, setIsTopic] = useState(false);
  const { showLoader, hideLoader } = useLoader();
  const { showSnackbar } = useSnackbar();

  const animatedHeight = useRef(new Animated.Value(0)).current;
  const animatedOpacity = useRef(new Animated.Value(0)).current;

  const [topicOptions, setTopicOptions] = useState<string[]>(routeParams.topics || []);

  // const getTopics = async () => {
  //   try {
  //     const { data, success } = await GETreq('support/topics');
  //     console.log('Topics', JSON.stringify(data, null, 3));
  //     if (success) {
  //       setTopicOptions(data?.data?.topics);
  //     }
  //   } catch (error) {
  //     console.log(error);
  //   }
  // };

  // useEffect(() => {
  //   getTopics();
  // }, []);

  useEffect(() => {
    if (isTopic) {
      Animated.parallel([
        Animated.timing(animatedHeight, {
          toValue: 1,
          duration: 300,
          useNativeDriver: false,
        }),
        Animated.timing(animatedOpacity, {
          toValue: 1,
          duration: 300,
          useNativeDriver: false,
        }),
      ]).start();
    } else {
      Animated.parallel([
        Animated.timing(animatedHeight, {
          toValue: 0,
          duration: 300,
          useNativeDriver: false,
        }),
        Animated.timing(animatedOpacity, {
          toValue: 0,
          duration: 300,
          useNativeDriver: false,
        }),
      ]).start();
    }
  }, [isTopic, animatedHeight, animatedOpacity]);

  const maxHeight = animatedHeight.interpolate({
    inputRange: [0, 1],
    outputRange: [0, 200],
  });

  const handleConfirmSubmit = async () => {
    try {
      showLoader();
      const payload = {
        topic,
        subject,
        message,
      };
      const { data, success } = await POSTreq('support/submit', payload, true);
      console.log('Support Data::', JSON.stringify(data, null, 3));
      if (success) {
        navigation.navigate('SupportSuccessScr', { message: data?.message });
      } else {
        showSnackbar(data?.message);
      }
    } catch (error) {
      console.log(error);
    } finally {
      hideLoader();
    }
  }

  const handleSubmit = async () => {
    let hasError = false;
    if (!topic) {
      setTopicError(t('TOPIC_REQUIRED'));
      hasError = true;
    } else {
      setTopicError('');
      hasError = false;
    }
    if (!subject.trim()) {
      subjectRef.current?.setError(true, t('PLS_FILL_THE_INPUT'));
      hasError = true;
    } else {
      subjectRef.current?.clearError();
      hasError = false;
    }
    if (!message.trim()) {
      messageRef.current?.setError(true, t('PLS_FILL_THE_INPUT'));
      hasError = true;
    } else {
      messageRef.current?.clearError();
      hasError = false;
    }

    if (hasError) {
      return;
    }
    handleConfirmSubmit();
  };

  return (
    <View style={[styles.container, { paddingTop: insets.top + 12 }]}>
      <View style={styles.row}>
        <TouchableOpacity
          style={styles.backBtn}
          onPress={() => navigation.goBack()}
        >
          <BACK_BTN_IC />
        </TouchableOpacity>
        <Text allowFontScaling={false} style={styles.title}>{t('SUPPORT')}</Text>
      </View>

      <View style={styles.inputWrapper}>
        <Text allowFontScaling={false} style={styles.label}>{t('TOPIC')}</Text>
        <TouchableOpacity
          style={styles.menuContainer}
          onPress={() => setIsTopic(!isTopic)}
        >
          <Text allowFontScaling={false} style={topic ? styles.selectedText : styles.selectText}>
            {topic || t('SELECT')}
          </Text>
          <DOWN_IC />
        </TouchableOpacity>
        {topicError && <Text allowFontScaling={false} style={styles.errorText}>{topicError}</Text>}
        <Animated.View
          style={[
            styles.topicListContainer,
            {
              maxHeight: maxHeight,
              opacity: animatedOpacity,
            },
          ]}
          pointerEvents={isTopic ? 'auto' : 'none'}
        >
          <FlatList
            data={topicOptions}
            keyExtractor={item => item}
            bounces={false}
            renderItem={({ item }) => (
              <TouchableOpacity
                onPress={() => {
                  setTopic(item);
                  setIsTopic(false);
                }}
              >
                <Text allowFontScaling={false} style={styles.topicLabel}>{item}</Text>
              </TouchableOpacity>
            )}
          />
        </Animated.View>

        <InputField
          label={t('SUBJECT')}
          placeholder={t('ENTER_SUBJECT')}
          value={subject}
          onChangeText={setSubject}
          containerStyle={styles.inputContainer}
          inputContainerStyle={styles.input}
          ref={subjectRef}
        />
        <InputField
          label={t('MESSAGE')}
          placeholder={t('ENTER_MESSAGE')}
          value={message}
          onChangeText={setMessage}
          containerStyle={styles.inputContainer}
          inputContainerStyle={styles.input}
          multiline={true}
          ref={messageRef}
        />
      </View>
      <View style={styles.btnContainer}>
        <BTN title={t('SUBMIT')} onP={handleSubmit} borderR={56} />
      </View>
    </View>
  );
};

export default SupportScr;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.WHITE,
  },
  backBtn: {
    position: 'absolute',
    left: 16,
    zIndex: 1,
  },
  title: {
    fontSize: 18,
    flex: 1,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.FINAL_BLACK,
    textAlign: 'center',
    top: isIOS ? 4 : 2,
  },
  row: {
    flexDirection: 'row',
    borderBottomWidth: 1,
    borderBottomColor: _COL.BORDER,
    paddingBottom: isIOS ? 18 : 12,
  },
  label: {
    fontSize: 14,
    color: _COL.FINAL_BLACK,
    fontFamily: FONT.MEDIUM,
  },
  menuContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingLeft: 15,
    paddingRight: 13,
    marginTop: 4,
    borderWidth: 1.5,
    borderColor: _COL.BORDER,
    borderRadius: 12,
    paddingVertical: 12,
  },
  selectText: {
    fontSize: 14,
    color: _COL.TEXT_GREY_LIGHT,
    fontFamily: FONT.REGULAR,
  },
  inputWrapper: {
    marginTop: 16,
    paddingHorizontal: 16,
  },
  btnContainer: {
    paddingHorizontal: 24,
    marginTop: 24,
  },
  inputContainer: {
    marginTop: 16,
  },
  input: {
    paddingLeft: 15,
  },
  topicListContainer: {
    marginHorizontal: 16,
    backgroundColor: _COL.WHITE,
    borderWidth: 1.5,
    borderColor: _COL.BORDER,
    borderRadius: 12,
    marginTop: 4,
    position: 'absolute',
    top: 76,
    left: 0,
    right: 0,
    zIndex: 10,
    elevation: 5,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    overflow: 'hidden',
  },
  topicLabel: {
    padding: 12,
    fontSize: 14,
    color: _COL.FINAL_BLACK,
    fontFamily: FONT.REGULAR,
    borderBottomWidth: 1,
    borderBottomColor: _COL.BORDER,
  },
  selectedText: {
    fontSize: 14,
    color: _COL.FINAL_BLACK,
    fontFamily: FONT.REGULAR,
  },
  errorText: {
    fontSize: 12,
    color: _COL.ERROR,
    marginTop: 4,
    fontFamily: FONT.SEMI_BOLD,
  }

});
