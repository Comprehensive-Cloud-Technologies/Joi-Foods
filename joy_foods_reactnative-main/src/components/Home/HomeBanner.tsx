import { memo, useEffect, useMemo, useRef, useState } from 'react';
import {
  View,
  FlatList as RNFlatList,
  StyleSheet,
  Pressable,
  Image,
} from 'react-native';
import { _COL, _W } from 'utils';
import { IBanner } from 'types';

const ITEM_WIDTH = _W * 0.85;
const ITEM_SPACING = 12;
const SIDE_PADDING = (_W - ITEM_WIDTH) / 2;

interface HomeBannerProps {
  banners: IBanner[];
  onBannerPress?: (banner: IBanner) => void;
}

const HomeBanner = ({ banners, onBannerPress }: HomeBannerProps) => {
  const [activeIndex, setActiveIndex] = useState(0);
  const [currentIndex, setCurrentIndex] = useState(banners.length > 1 ? 1 : 0);
  const flatListRef = useRef<RNFlatList | null>(null);

  if (!banners || banners.length === 0) {
    return null;
  }

  const isInfinite = banners.length > 1;

  const sortedBanners = useMemo(() => {
    return [...banners].sort(
      (a, b) => (a.display_order || 0) - (b.display_order || 0),
    );
  }, [banners]);

  const infiniteData = useMemo(() => {
    if (!isInfinite) return sortedBanners;
    return [
      { ...sortedBanners[sortedBanners.length - 1], id: -1 },
      ...sortedBanners,
      { ...sortedBanners[0], id: -2 },
    ];
  }, [sortedBanners, isInfinite]);

  useEffect(() => {
    if (!isInfinite) return;

    const timer = setInterval(() => {
      let nextIndex = currentIndex + 1;

      if (nextIndex >= infiniteData.length) {
        nextIndex = 2;
      }

      flatListRef.current?.scrollToIndex({
        index: nextIndex,
        animated: true,
      });
      setCurrentIndex(nextIndex);
    }, 5000);

    return () => clearInterval(timer);
  }, [currentIndex, isInfinite, infiniteData.length]);

  const onScroll = (ev: any) => {
    const x = ev.nativeEvent.contentOffset.x;
    const index = Math.round(x / (ITEM_WIDTH + ITEM_SPACING));

    let nextActiveIndex = index - 1;
    if (nextActiveIndex < 0) nextActiveIndex = sortedBanners.length - 1;
    if (nextActiveIndex >= sortedBanners.length) nextActiveIndex = 0;

    if (nextActiveIndex !== activeIndex) {
      setActiveIndex(nextActiveIndex);
    }
  };

  const handleScrollEnd = (ev: any) => {
    if (!isInfinite) {
      const x = ev.nativeEvent.contentOffset.x;
      const index = Math.round(x / (ITEM_WIDTH + ITEM_SPACING));
      setCurrentIndex(index);
      return;
    }

    const x = ev.nativeEvent.contentOffset.x;
    const index = Math.round(x / (ITEM_WIDTH + ITEM_SPACING));

    if (index <= 0) {
      const realLastIndex = sortedBanners.length;
      flatListRef.current?.scrollToIndex({
        index: realLastIndex,
        animated: false,
      });
      setCurrentIndex(realLastIndex);
      setActiveIndex(sortedBanners.length - 1);
    } else if (index >= infiniteData.length - 1) {
      flatListRef.current?.scrollToIndex({
        index: 1,
        animated: false,
      });
      setCurrentIndex(1);
      setActiveIndex(0);
    } else {
      setCurrentIndex(index);
    }
  };

  return (
    <View style={styles.carouselWrapper}>
      <RNFlatList
        ref={flatListRef}
        data={infiniteData}
        keyExtractor={(item, index) => `${item.id}-${index}`}
        horizontal
        showsHorizontalScrollIndicator={false}
        snapToInterval={ITEM_WIDTH + ITEM_SPACING}
        decelerationRate="fast"
        bounces={false}
        initialScrollIndex={isInfinite ? 1 : 0}
        getItemLayout={(_, index) => ({
          length: ITEM_WIDTH + ITEM_SPACING,
          offset: (ITEM_WIDTH + ITEM_SPACING) * index,
          index,
        })}
        contentContainerStyle={{
          paddingHorizontal: SIDE_PADDING - ITEM_SPACING / 2,
        }}
        onScroll={onScroll}
        scrollEventThrottle={16}
        onMomentumScrollEnd={handleScrollEnd}
        renderItem={({ item, index }) => {
          return (
            <Pressable
              style={{
                width: ITEM_WIDTH,
                marginHorizontal: ITEM_SPACING / 2,
                borderRadius: 19,
                overflow: 'hidden',
              }}
              onPress={() => onBannerPress?.(item)}
            >
              <Image
                source={{ uri: item.image_url }}
                style={{ flex: 1, borderRadius: 20 }}
              />
            </Pressable>
          );
        }}
      />

      {isInfinite && (
        <View style={styles.pagination}>
          {sortedBanners.map((_, i) => (
            <View
              key={i}
              style={[styles.dot, i === activeIndex && styles.activeDot]}
            />
          ))}
        </View>
      )}
    </View>
  );
};

export default memo(HomeBanner);

const styles = StyleSheet.create({
  carouselWrapper: {
    height: 220,
  },
  pagination: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 14,
  },
  dot: {
    width: 5,
    height: 5,
    borderRadius: 4,
    backgroundColor: _COL.SECONDARY_RED,
    marginHorizontal: 4,
  },
  activeDot: {
    backgroundColor: _COL.PRIMARY_RED,
    width: 20,
  },
});
