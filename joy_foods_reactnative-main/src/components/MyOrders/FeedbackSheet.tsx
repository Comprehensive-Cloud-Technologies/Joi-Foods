import TextButton from 'components/TextButton';
import BottomActionSheet from 'components/ui/BottomActionSheet';
import BTN from 'components/ui/BTN';
import { useT } from 'internationalization';
import { memo, useState } from 'react';
import { ScrollView, StyleSheet, Text, TextInput, TouchableOpacity, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { _COL, FONT } from 'utils';
import { useLoader, useSnackbar } from 'components';
import { POSTreq } from 'api';

const FeedbackSheet = ({
  isVisible,
  setIsVisible,
  orderId,
  onSuccess,
}: {
  isVisible: boolean;
  setIsVisible: () => void;
  orderId?: number;
  onSuccess?: () => void;
}) => {
  const { t } = useT();
  const { bottom } = useSafeAreaInsets();
  const { showLoader, hideLoader } = useLoader();
  const { showSnackbar } = useSnackbar();

  const [isFood, setIsFood] = useState(true);
  const [isService, setIsService] = useState(false);
  const [isComments, setIsComments] = useState(false);

  const [foodReview, setFoodReview] = useState('');
  const [serviceReview, setServiceReview] = useState('');
  const [extraComments, setExtraComments] = useState('');

  const handleSubmit = async () => {
    if (foodReview.length === 0 && serviceReview.length === 0 && extraComments.length === 0) {
      showSnackbar(t('PLEASE_ADD_SOME_FEEDBACK'));
      return;
    }

    try {
      showLoader();
      const payload = {
        order_id: orderId,
        food_review: foodReview,
        service_review: serviceReview,
        extra_comments: extraComments,
      };
      console.log('Feedback Payload::', JSON.stringify(payload, null, 3));
      const { success, data } = await POSTreq('reviews/submit_review', payload, true);
      console.log('Feedback Response::', JSON.stringify(data, null, 3));

      if (success) {
        showSnackbar(data?.message || t('FEEDBACK_SUBMITTED_SUCCESSFULLY'));
        onSuccess?.();
        setIsVisible();
        setFoodReview('');
        setServiceReview('');
        setExtraComments('');
      } else {
        showSnackbar(data?.message || t('ERROR_OCCURED'));
      }
      hideLoader();
    } catch (error) {
      console.log('Feedback Error::', error);
      showSnackbar(t('ERROR_OCCURED'));
      hideLoader();
    }
  };

  const getActiveValue = () => {
    if (isFood) return foodReview;
    if (isService) return serviceReview;
    return extraComments;
  };

  const handleTextChange = (text: string) => {
    if (isFood) setFoodReview(text);
    else if (isService) setServiceReview(text);
    else setExtraComments(text);
  };

  return (
    <BottomActionSheet
      visible={isVisible}
      onClose={setIsVisible}
      showHandle={true}
    >
      <ScrollView
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}
      >
        <Text allowFontScaling={false} style={styles.feedbackHeading}>{t('GIVE_YOUR_FEEDBACK')}</Text>
        <Text allowFontScaling={false} style={styles.feedbackSubheading}>
          {t('YOUR_FEEDBACK_HELPS_US_IMPROVE')}
        </Text>

        <View style={styles.feedbackTypesContainer}>
          <Text allowFontScaling={false} style={styles.feedbackTypeText}>{t('TYPE')}</Text>
          <View style={styles.feedbackTypeOptions}>
            <TouchableOpacity
              onPress={() => {
                setIsFood(true);
                setIsService(false);
                setIsComments(false);
              }}
              style={[
                styles.feedbackTypeOption,
                isFood && styles.feedbackTypeOptionActive,
              ]}
            >
              <Text allowFontScaling={false}
                style={[
                  styles.feedbackTypeOptionText,
                  isFood && styles.feedbackTypeOptionTextActive,
                ]}
              >
                {t('FOODS')}
              </Text>
            </TouchableOpacity>
            <TouchableOpacity
              onPress={() => {
                setIsFood(false);
                setIsService(true);
                setIsComments(false);
              }}
              style={[
                styles.feedbackTypeOption,
                isService && styles.feedbackTypeOptionActive,
              ]}
            >
              <Text allowFontScaling={false}
                style={[
                  styles.feedbackTypeOptionText,
                  isService && styles.feedbackTypeOptionTextActive,
                ]}
              >
                {t('SERVICES')}
              </Text>
            </TouchableOpacity>
            <TouchableOpacity
              onPress={() => {
                setIsFood(false);
                setIsService(false);
                setIsComments(true);
              }}
              style={[
                styles.feedbackTypeOption,
                isComments && styles.feedbackTypeOptionActive,
              ]}
            >
              <Text allowFontScaling={false}
                style={[
                  styles.feedbackTypeOptionText,
                  isComments && styles.feedbackTypeOptionTextActive,
                ]}
              >
                {t('COMMENTS')}
              </Text>
            </TouchableOpacity>
          </View>
        </View>

        <View style={styles.descriptionContainer}>
          <Text allowFontScaling={false} style={styles.descriptionLabel}>{t('DESCRIPTION')}</Text>
          <TextInput
            placeholder={t('FEEDBACK_DESCRIPTION')}
            placeholderTextColor={_COL.TEXT_GREY_LIGHT}
            multiline={true}
            allowFontScaling={false}
            numberOfLines={4}
            scrollEnabled={false}
            value={getActiveValue()}
            onChangeText={handleTextChange}
            style={styles.descriptionInput}
          />
        </View>
        <View style={styles.btnContainer}>
          <BTN title={t('SUBMIT')} onP={handleSubmit} borderR={120} mTop={13} />
        </View>
        <TextButton
          onP={setIsVisible}
          isUnderline={true}
          style={{ paddingBottom: bottom }}
          text={t('CANCEL')}
        />
      </ScrollView>
    </BottomActionSheet>
  );
};

export default memo(FeedbackSheet);

const styles = StyleSheet.create({
  btnContainer: {
    marginHorizontal: 24,
  },
  cancelBtnText: {
    fontSize: 14,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.FINAL_BLACK,
    alignItems: 'center',
    marginTop: 18,
    textDecorationLine: 'underline',
    textDecorationStyle: 'solid',
  },
  feedbackHeading: {
    fontSize: 22,
    fontFamily: FONT.BOLD,
    color: _COL.FINAL_BLACK,
    textAlign: 'left',
    marginTop: 27,
    marginLeft: 21,
  },
  feedbackSubheading: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    color: _COL.MAIN_BLACK,
    textAlign: 'left',
    marginTop: 10,
    marginBottom: 16,
    marginLeft: 21,
  },
  feedbackTypesContainer: {
    marginBottom: 16,
    paddingHorizontal: 16,
  },
  feedbackTypeText: {
    fontSize: 14,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.MAIN_BLACK,
    marginBottom: 8,
  },
  feedbackTypeOptions: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    backgroundColor: _COL.SECONDARY_BG,
    borderRadius: 66,
  },
  feedbackTypeOption: {
    paddingVertical: 10,
    paddingHorizontal: 30,
  },
  feedbackTypeOptionActive: {
    backgroundColor: _COL.WHITE,
    borderRadius: 66,
    borderWidth: 1,
    borderColor: _COL.PRIMARY_RED,
  },
  feedbackTypeOptionText: {
    fontSize: 14,
    fontFamily: FONT.MEDIUM,
    color: _COL.TEXT_GREY_LIGHT,
    alignSelf: 'center',
  },
  feedbackTypeOptionTextActive: {
    color: _COL.PRIMARY_RED,
  },
  descriptionContainer: {
    marginHorizontal: 20,
    marginTop: 8,
  },
  descriptionInput: {
    borderWidth: 1,
    borderColor: _COL.BORDER,
    borderRadius: 8,
    padding: 10,
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    color: _COL.BLACK,
    minHeight: 100,
    textAlignVertical: 'top',
  },
  descriptionLabel: {
    fontSize: 14,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.FINAL_BLACK,
    marginBottom: 4,
  },
});
