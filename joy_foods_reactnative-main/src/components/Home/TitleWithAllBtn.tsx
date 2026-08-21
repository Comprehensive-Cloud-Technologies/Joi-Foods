import {
  StyleProp,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
  ViewStyle,
} from 'react-native';
import { _COL, FONT } from 'utils';

const TitleWithAllBtn = ({
  title,
  onPress,
  viewAllText,
  containerStyle,
}: {
  title: string;
  onPress: () => void;
  viewAllText: string;
  containerStyle?: StyleProp<ViewStyle>;
}) => {
  return (
    <View style={[styles.categoryContainer, containerStyle]}>
      <Text allowFontScaling={false} style={styles.categoryText}>{title}</Text>
      <TouchableOpacity onPress={onPress}>
        <Text allowFontScaling={false} style={styles.viewAllText}>{viewAllText}</Text>
      </TouchableOpacity>
    </View>
  );
};

export default TitleWithAllBtn;

const styles = StyleSheet.create({
  categoryContainer: {
    marginTop: 16,
    marginHorizontal: 16,
    justifyContent: 'space-between',
    alignItems: 'center',
    flexDirection: 'row',
  },
  categoryText: {
    fontSize: 16,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.FINAL_BLACK,
    lineHeight: 24,
  },
  viewAllText: {
    fontSize: 14,
    fontFamily: FONT.MEDIUM,
    color: _COL.TEXT_GREY,
    lineHeight: 24,
  },
});
