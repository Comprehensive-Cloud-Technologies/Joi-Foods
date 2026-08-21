import React, { useState, useEffect } from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import { ScrollView } from 'react-native-gesture-handler';
import BottomActionSheet from './ui/BottomActionSheet';
import { _COL, FONT } from 'utils';
import { CLOCK2_IC } from 'assets';
import BTN from './ui/BTN';
import { useT } from 'internationalization';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import TextButton from './TextButton';

interface DateItem {
  day: string;
  date: number;
  fullDate: string;
  isToday: boolean;
}

const ScheduleCalendarSheet = ({
  isBooking,
  setIsBooking,
  onBook,
  data,
  selectedDays,
  error,
}: {
  isBooking: boolean;
  setIsBooking: (val: boolean) => void;
  onBook: (val: string[]) => void;
  data?: any;
  selectedDays?: any;
  error?: string;
}) => {
  const { t } = useT();
  const { bottom } = useSafeAreaInsets();
  const [selectedDates, setSelectedDates] = useState<string[]>([]);
  const [dates, setDates] = useState<DateItem[]>([]);
  const [selectedD, setSelectedD] = useState(selectedDays || null);
  const [err, setErr] = useState<string | null>(error || null);

  useEffect(() => {
    const today = new Date();
    const datesArray: DateItem[] = Array.from({ length: 8 }, (_, i) => {
      const date = new Date(today);
      date.setDate(today.getDate() + i);

      return {
        day: date
          .toLocaleDateString('en-US', { weekday: 'short' })
          .toUpperCase(),
        date: date.getDate(),
        fullDate: date.toISOString().split('T')[0],
        isToday: i === 0,
      };
    });

    setDates(datesArray);
  }, []);

  const toggleDateSelection = (fullDate: string): void => {
    setSelectedDates(prev => {
      const index = prev.indexOf(fullDate);
      if (index > -1) {
        return prev.filter(date => date !== fullDate);
      }
      return [...prev, fullDate].sort();
    });

  };

  useEffect(() => {
    if (selectedD) {
      selectedD.map((date: any) => {
        console.log('selectedD date:', date.date);
        setSelectedDates(prev => {
          if (!prev.includes(date.date)) {
            return [...prev, date.date].sort();
          }
          return prev;
        });
      });
    }
  }, [selectedD]);

  useEffect(() => {
    if (error) {
      setErr(error);
    }
  }, [error]);

  const isDateSelected = (fullDate: string): boolean => selectedDates.includes(fullDate);

  const handleBook = () => {
    if (!selectedDates || selectedDates.length === 0) {
      setErr(t('PLEASE_SELECT_ATLEAST_ONE_DATE'));
      return;
    }
    onBook(selectedDates);
  }


  return (
    <BottomActionSheet
      visible={isBooking}
      onClose={() => setIsBooking(false)}
      showHandle={true}
    >
      <View style={styles.bookingScheduleContainer}>
        <Text allowFontScaling={false} style={styles.bookingScheduleHeading}>
          {t('BOOKING_SCHEDULE')}
        </Text>
        <Text allowFontScaling={false} style={styles.chooseDaysText}>{t('CHOOSE_DAYS')}</Text>

        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          contentContainerStyle={styles.scrollContent}
        >
          {dates.map((item: DateItem, index: number) => {
            const isSelected = isDateSelected(item.fullDate);

            return (
              <TouchableOpacity
                key={`${item.fullDate}-${index}`}
                disabled={!data?.today?.available && item.isToday}
                style={[
                  styles.dateCard,
                  {
                    backgroundColor: isSelected ? _COL.THIRD_BG : _COL.WHITE,
                    borderColor: isSelected
                      ? _COL.PRIMARY_RED
                      : _COL.BORDER_FIFTH,
                    borderWidth: isSelected ? 1 : 1.5,
                    opacity: !data?.today?.available && item.isToday ? 0.5 : 1,
                  },
                ]}
                onPress={() => toggleDateSelection(item.fullDate)}
              >
                <Text allowFontScaling={false} style={[styles.dayText]}>{item.day}</Text>
                <Text allowFontScaling={false} style={[styles.dateText]}>{item.date}</Text>
              </TouchableOpacity>
            );
          })}
        </ScrollView>

        {err && (
          <Text allowFontScaling={false} style={{ color: 'red', textAlign: 'center', marginTop: 8 }}>
            {err}
          </Text>
        )}

        {data?.meal_type && data?.store_timings?.[`${data?.meal_type.toLowerCase()}_time`] && (
          <View style={styles.lunchTimeContainer}>
            <CLOCK2_IC />
            <Text allowFontScaling={false} style={styles.lunchTimeText}>
              {data?.meal_type.charAt(0).toUpperCase() + data?.meal_type.slice(1).toLowerCase()} timing :
              {` ${data?.store_timings?.[`${data?.meal_type.toLowerCase()}_time`]}`}
            </Text>
          </View>
        )}

        <BTN
          title={t('BOOK')}
          onP={handleBook}
          borderR={120}
          mTop={27}
        />

        {/* <TouchableOpacity
          style={{ alignSelf: 'center', paddingBottom: bottom + 16 }}
          onPress={() => setIsBooking(false)}
        >
          <Text allowFontScaling={false} style={styles.cancelBookingText}>{t('CANCEL')}</Text>
        </TouchableOpacity> */}
        <TextButton
          onP={() => setIsBooking(false)}
          isUnderline={true}
          style={{ paddingBottom: bottom, paddingTop: 2 }}
          text={t('CANCEL')}
        />
      </View>
    </BottomActionSheet>
  );
};

const styles = StyleSheet.create({
  scrollContent: {
    paddingRight: 20,
    marginTop: 12,
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
  bookingScheduleContainer: {
    paddingHorizontal: 16,
    paddingTop: 27,
  },
  bookingScheduleHeading: {
    fontSize: 22,
    fontFamily: FONT.BOLD,
    color: _COL.FINAL_BLACK,
    lineHeight: 30,
  },
  chooseDaysText: {
    fontSize: 14,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.FINAL_BLACK,
    marginTop: 30,
    lineHeight: 24,
  },
  lunchTimeContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 28,
  },
  lunchTimeText: {
    fontSize: 12,
    fontFamily: FONT.MEDIUM,
    color: _COL.TEXT_GREY_LIGHT,
    marginLeft: 6,
    lineHeight: 16
  },
  cancelBookingText: {
    fontSize: 14,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.FINAL_BLACK,
    marginTop: 18,
    textDecorationLine: 'underline',
    textDecorationStyle: 'solid',
  },
});

export default ScheduleCalendarSheet;
