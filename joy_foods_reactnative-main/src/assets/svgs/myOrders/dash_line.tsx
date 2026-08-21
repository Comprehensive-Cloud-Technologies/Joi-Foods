import React from 'react';
import { View, StyleSheet, StyleProp, ViewStyle } from 'react-native';
import Svg, { Line } from 'react-native-svg';

interface VerticalDashedLineProps {
  color?: string;
  thickness?: number;
  dashPattern?: string;
  style?: StyleProp<ViewStyle>;
}

const VerticalDashedLine: React.FC<VerticalDashedLineProps> = ({
  color = '#000',
  thickness = 2,
  dashPattern = '6,4',
  style,
}) => {
  return (
    <View style={[{ flex: 1, alignItems: 'center' }, style]}>
      <Svg style={[StyleSheet.absoluteFill, { width: thickness }]}>
        <Line
          y1={thickness / 2}
          x1="0"
          y2={thickness / 2}
          x2="100%"
          stroke={color}
          strokeWidth={thickness}
          strokeDasharray={dashPattern}
        />
      </Svg>
    </View>
  );
};

export default VerticalDashedLine;
