import React from 'react';
import { View, StyleSheet, ViewStyle } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

type Props = {
  children: React.ReactNode;
  style?: ViewStyle;
};

const SafeInsets: React.FC<Props> = ({ children, style }) => {
  const insets = useSafeAreaInsets();

  return (
    <View
      style={[
        styles.container,
        {
          paddingTop: insets.top,
          // paddingBottom: insets.bottom,
          
        },
        style,
      ]}
    >
      {children}
    </View>
  );
};

export default SafeInsets;

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
});
