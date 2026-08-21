import {
  View,
  Text,
  Image,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
} from 'react-native';
import React, { useRef, useState } from 'react';
import { _COL, _W, FONT } from 'utils';
import { useT } from 'internationalization';
import { useAtom } from 'jotai';
import { isonboardingAtom } from 'store';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { RIGHT_ARROW_IC, RIGHT_IC } from 'assets';
import { StackProps } from 'types';

const OnboardingScreen = ({ navigation }: StackProps<'OnboardingScreen'>) => {
  const { t } = useT();
  const insets = useSafeAreaInsets();
  const scrollRef = useRef<ScrollView>(null);
  const [page, setPage] = useState(0);
  const [, setIsOnboarding] = useAtom(isonboardingAtom);

  const pages = [
    {
      title: t('BEST_DAILY_MEAL'),
      description: t('BEST_DAILY_MEAL_DESC'),
      image: require('../assets/images/onboarding/onboarding1.png'),
    },
    {
      title: t('ORDER_SNACKS'),
      description: t('ORDER_SNACKS_DESC'),
      image: require('../assets/images/onboarding/onboarding2.png'),
    },
    {
      title: t('GET_FOOD_MEETINGS'),
      description: t('GET_FOOD_MEETINGS_DESC'),
      image: require('../assets/images/onboarding/onboarding3.png'),
    },
  ];
  const handleSkip = () => {
    setIsOnboarding(true);
    navigation.replace('CompanyCode');
  };
  const onScroll = (event: any) => {
    const currentPage = Math.round(event.nativeEvent.contentOffset.x / _W);
    setPage(currentPage);
  };

  return (
    <View style={styles.container}>
      <ScrollView
        ref={scrollRef}
        horizontal
        pagingEnabled
        showsHorizontalScrollIndicator={false}
        onScroll={onScroll}
        scrollEventThrottle={16}
        bounces={false}
      >
        {pages.map((p, index) => (
          <View key={index} style={{ width: _W }}>
            <Image source={p.image} style={styles.image} />
            <Text allowFontScaling={false} style={styles.title}>{p.title}</Text>
            <Text allowFontScaling={false} style={styles.description}>{p.description}</Text>
          </View>
        ))}
      </ScrollView>

      {/* Don't show skip button at last page */}
      {page !== pages.length - 1 && (
        <TouchableOpacity
          style={[styles.skipBtn, { top: insets.top + 19 }]}
          onPress={handleSkip}
        >
          <Text allowFontScaling={false}>{t('SKIP')}</Text>
        </TouchableOpacity>
      )}

      <View style={[styles.buttonDotContainer, { bottom: insets.bottom + 30 }]}>
        <View style={styles.dotContainer}>
          {pages.map((_, i) => (
            <View
              key={i}
              style={[styles.dot, page === i && styles.activeDot]}
            />
          ))}
        </View>

        <TouchableOpacity
          activeOpacity={0.7}
          style={[
            styles.rightArrow,
            {
              paddingHorizontal: page == pages.length - 1 ? 16.5 : 22,
              paddingVertical: page == pages.length - 1 ? 19.5 : 19,
              backgroundColor:
                page == pages.length - 1 ? _COL.PRIMARY_RED : _COL.FINAL_BLACK,
            },
          ]}
          onPress={() => {
            if (page == pages.length - 1) {
              handleSkip();
            } else if (scrollRef.current) {
              scrollRef.current.scrollTo({
                x: _W * (page + 1),
                animated: true,
              });
            }
          }}
        >
          {page == pages.length - 1 ? <RIGHT_IC /> : <RIGHT_ARROW_IC />}
        </TouchableOpacity>
      </View>
    </View>
  );
};

export default OnboardingScreen;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: _COL.WHITE,
  },
  image: {
    width: '100%',
    height: "65%"
  },
  title: {
    fontSize: 32,
    color: _COL.TEXT_BLACK_DARK,
    fontFamily: FONT.SEMI_BOLD,
    paddingHorizontal: 30,
    textAlign: 'left',
  },
  description: {
    fontSize: 16,
    color: _COL.TEXT_BLACK,
    fontFamily: FONT.REGULAR,
    textAlign: 'left',
    paddingHorizontal: 30,
    marginTop: 10,
  },
  dot: {
    width: 8,
    height: 8,
    borderRadius: 8,
    backgroundColor: _COL.LIGHT_GREY,
  },
  activeDot: {
    backgroundColor: _COL.FINAL_BLACK,
    width: 18,
  },
  buttonDotContainer: {
    width: '100%',
    flexDirection: 'row',
    position: 'absolute',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingLeft: 30,
    paddingRight: 24,
  },
  dotContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    gap: 4,
  },

  rightArrow: {
    borderRadius: 30,
    backgroundColor: _COL.FINAL_BLACK,
    paddingHorizontal: 22,
    paddingVertical: 19,
  },
  skipBtn: {
    position: 'absolute',
    fontSize: 16,
    color: _COL.TEXT_GREY_MEDIUM,
    fontFamily: FONT.SEMI_BOLD,
    right: 20,
  },
});
