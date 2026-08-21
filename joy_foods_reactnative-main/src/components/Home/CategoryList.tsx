import React, { memo } from 'react';
import { Image, FlatList, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { _COL, _W, FONT } from 'utils';
import { useT } from 'internationalization';
import { CategoryListT, IStoreData } from 'types';
import TitleWithAllBtn from './TitleWithAllBtn';
import { decodeHtml } from 'function';
import StoreType from 'types/StoreTypes';

const CategoryList = ({
  onViewAllPress,
  onCategoryPress,
  sortedCategories,
  storeData
}: {
  onViewAllPress: () => void;
  onCategoryPress?: (item: CategoryListT) => void;
  sortedCategories: CategoryListT[];
  storeData: IStoreData;
}) => {
  const { t } = useT();
  const isKOT = storeData?.store_type === StoreType.KOT;

  const renderItem = ({ item }: { item: CategoryListT }) => (
    <TouchableOpacity
      key={item.id}
      style={[styles.categoryItem, { width: isKOT ? _W / 3.45 : _W / 4.5 }]}
      onPress={() => {
        onCategoryPress?.(item);
      }}
    >
      <Image source={{ uri: item.icon }} style={styles.categoryItemImage} />
      <Text allowFontScaling={false} style={styles.categoryItemText}>{decodeHtml(item.name)}</Text>
    </TouchableOpacity>
  );

  if (!sortedCategories || sortedCategories.length === 0) {
    return null;
  }

  return (
    <View style={{ marginTop: !isKOT ? 24 : 0, gap: 12 }}>
      {!isKOT && (
        <TitleWithAllBtn
          title={t('CATEGORY')}
          onPress={onViewAllPress}
          viewAllText={t('VIEW_ALL')}
          containerStyle={{ marginTop: 0 }}
        />
      )}
      <FlatList
        data={sortedCategories}
        renderItem={renderItem}
        keyExtractor={item => item.id.toString()}
        horizontal
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.categoryItemContentContainer}
      />
    </View>
  );
};

export default memo(CategoryList);

const styles = StyleSheet.create({
  categoryItemContentContainer: {
    paddingLeft: 16,
    paddingRight: 8,
  },
  categoryItem: {
    width: _W / 4.5,
    alignItems: 'center',
    backgroundColor: _COL.WHITE,
    borderWidth: 1,
    borderColor: _COL.BORDER_FOURTH,
    padding: 10,
    borderRadius: 12,
    marginRight: 12,
  },
  categoryItemText: {
    marginTop: 6,
    fontSize: 12,
    fontFamily: FONT.REGULAR,
    color: _COL.FINAL_BLACK,
    textAlign: 'center',
  },
  categoryItemImage: {
    width: 52,
    height: 52,
    borderRadius: 26,
  },
});
