import TextButton from 'components/TextButton';
import BottomActionSheet from 'components/ui/BottomActionSheet';
import BTN from 'components/ui/BTN';
import InputField, { DynamicInputRef } from 'components/ui/InputField';
import { useT } from 'internationalization';
import { memo, useEffect, useRef, useState } from 'react';
import { ScrollView, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { _COL, FONT } from 'utils';

interface DateItem {
  day: string;
  date: number;
  fullDate: string;
  isToday: boolean;
}

const PreMealCancelOrderSheet = ({
  isVisible,
  setIsVisible,
  onProceed,
  selectDatesData
}: {
  isVisible: boolean;
  setIsVisible: () => void;
  onProceed: (order_ids: string) => void;
  selectDatesData: any
}) => {
  const { t } = useT();
  const { bottom } = useSafeAreaInsets();
  const [isEntireOrder, setIsEntireOrder] = useState(false);
  const [isSpecificOrder, setIsSpecificOrder] = useState(true);
  const [selectedDates, setSelectedDates] = useState<string[]>([]);
  const [selectedDaysError, setSelectedDaysError] = useState('');

  const toggleDateSelection = (fullDate: string): void => {
    setSelectedDates(prev => {
      const index = prev.indexOf(fullDate);
      if (index > -1) {
        return prev.filter(date => date !== fullDate);
      }
      return [...prev, fullDate].sort();
    });
  };

  const isDateSelected = (fullDate: string): boolean =>
    selectedDates.includes(fullDate);

  const handleCancelOrder = () => {
    if (selectedDates.length === 0) {
      setSelectedDaysError(t('PLS_SELECT_ATLEAST_ONE_DAY'));
      return;
    }
    // proccess cancellation with selectedDates,pass order ids as string
    onProceed(selectDatesData.filter((d: any) => selectedDates.includes(d.scheduled_date)).map((d: any) => d.itemID).join(','));
    console.log('Cancel order ids', selectDatesData.filter((d: any) => selectedDates.includes(d.scheduled_date)).map((d: any) => d.itemID).join(','));
  }

  return (
    <BottomActionSheet
      visible={isVisible}
      onClose={setIsVisible}
      showHandle={true}
    >
      <ScrollView
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}
        contentContainerStyle={{
          paddingHorizontal: 21,
        }}
      >
        <Text allowFontScaling={false} style={styles.cancelHeading}>{t('CANCEL_ORDER')}</Text>
        <Text allowFontScaling={false} style={styles.cancelDesc}>{t('SELECT_DAYS_CANCEL')}</Text>

        <View style={styles.cancelOrderOptionContainer}>
          <TouchableOpacity
            onPress={() => {
              setIsEntireOrder(true);
              setIsSpecificOrder(false);
              // entire order select every date selected except cancelled dates
              setSelectedDates(selectDatesData.filter((date: any) => !date.isCancelled).map((date: any) => date.scheduled_date));
            }}
            style={[styles.cancelOrderOption,
            isEntireOrder && { backgroundColor: _COL.WHITE, borderWidth: 1, borderColor: _COL.PRIMARY_RED }]}
          >
            <Text allowFontScaling={false} style={[styles.cancelOrderOptionText,
            isEntireOrder && { color: _COL.PRIMARY_RED, fontFamily: FONT.SEMI_BOLD }
            ]}>{t('ENTIRE_ORDER')}</Text>
          </TouchableOpacity>
          <TouchableOpacity
            onPress={() => {
              setIsSpecificOrder(true);
              setIsEntireOrder(false);
              // specific order deselect every date
              setSelectedDates([]);
            }}
            style={[styles.cancelOrderOption,
            isSpecificOrder && { backgroundColor: _COL.WHITE, borderWidth: 1, borderColor: _COL.PRIMARY_RED }]}
          >
            <Text allowFontScaling={false} style={[styles.cancelOrderOptionText,
            isSpecificOrder && { color: _COL.PRIMARY_RED, fontFamily: FONT.SEMI_BOLD }
            ]}>{t('SPECIFIC_DAY_ORDER')}</Text>
          </TouchableOpacity>
        </View>

        <Text allowFontScaling={false} style={styles.selectedDaysText}>{t('SELECTED_DAYS')}</Text>
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          contentContainerStyle={styles.scrollContent}
        >
          {selectDatesData?.map((item: any, index: number) => {
            const isSelected = isDateSelected(item.scheduled_date);

            return (
              <TouchableOpacity
                key={`${item.scheduled_date}-${index}`}
                style={[
                  styles.dateCard,
                  {
                    backgroundColor: isSelected ? _COL.THIRD_BG : _COL.WHITE,
                    borderColor: isSelected
                      ? _COL.PRIMARY_RED
                      : _COL.BORDER_FIFTH,
                    borderWidth: isSelected ? 1 : 1.5,
                  },
                ]}
                disabled={item.isCancelled}
                onPress={() => toggleDateSelection(item.scheduled_date)}
              >
                <Text allowFontScaling={false} style={[styles.dayText]}>{item.day}</Text>
                <Text allowFontScaling={false} style={[styles.dateText]}>{item.scheduled_date}</Text>
              </TouchableOpacity>
            );
          })}
        </ScrollView>

        {selectedDaysError && <Text allowFontScaling={false} style={styles.selectedDaysError}>{selectedDaysError}</Text>}

        <BTN
          title={t('CANCEL_ORDER')}
          onP={handleCancelOrder}
          borderR={120}
          mTop={25}
        />

        <TextButton
          onP={setIsVisible}
          isUnderline={true}
          style={{paddingBottom: bottom}}
          text={t('NO_KEEP_IT_ACTIVE')}
        />
      </ScrollView>
    </BottomActionSheet>
  );
};

export default memo(PreMealCancelOrderSheet);

const styles = StyleSheet.create({
  cancelHeading: {
    fontSize: 22,
    fontFamily: FONT.BOLD,
    color: _COL.FINAL_BLACK,
    textAlign: 'left',
    marginTop: 27,
    lineHeight: 30,
  },
  cancelDesc: {
    fontSize: 14,
    fontFamily: FONT.REGULAR,
    color: _COL.MAIN_BLACK,
    textAlign: 'left',
    marginTop: 4,
    lineHeight: 24,
  },
  cancelOrderOptionContainer: {
    flexDirection: 'row',
    backgroundColor: _COL.TRACK_ONE,
    borderRadius: 66,
    marginTop: 20,
  },
  cancelOrderOption: {
    borderRadius: 66,
    paddingVertical: 10,
    width: '50%',
  },
  cancelOrderOptionText: {
    fontSize: 14,
    fontFamily: FONT.MEDIUM,
    color: _COL.FINAL_BLACK,
    alignSelf: 'center',
  },
  scrollContent: {
    paddingRight: 20,
  },
  dateCard: {
    height: 78,
    width: 78,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
  },
  dayText: {
    fontSize: 12,
    lineHeight: 24,
    fontFamily: FONT.MEDIUM,
    marginBottom: 8,
    color: _COL.TEXT_GREY_LIGHT,
  },
  dateText: {
    fontSize: 20,
    lineHeight: 24,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.FINAL_BLACK,
  },
  selectedDaysError: {
    fontSize: 12,
    color: _COL.THIRD_RED,
    fontFamily: FONT.MEDIUM,
    marginTop: 8,
  },
  selectedDaysText: {
    fontSize: 14,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.FINAL_BLACK,
    marginTop: 20,
    marginBottom: 12,
  },
});
