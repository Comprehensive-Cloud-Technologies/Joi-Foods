import React from 'react';
import { View, DimensionValue } from 'react-native';
import Svg, { Line } from 'react-native-svg';

const DashedLine = ({ width = '100%' as DimensionValue, color = '#000', thickness = 1 }: { width?: DimensionValue; color?: string; thickness?: number }) => {
  // Define the dash length and gap length in an array: [dash, gap]
  const dashPattern = [5, 5]; 

  return (
    <View style={{ width , marginTop: 2}}>
      {/* 
        The Svg container should have a defined height and width.
        The height must be sufficient for the line's thickness (strokeWidth).
      */}
      <Svg height={thickness} width="100%">
        <Line
          x1="0" // Start at the beginning of the x-axis
          y1="0" // Start at the top of the y-axis
          x2="100%" // End at the end of the x-axis
          y2="0" // End at the top of the y-axis (horizontal line)
          stroke={color} // Line color
          strokeWidth={thickness} // Line thickness
          strokeDasharray={dashPattern} // The dash and gap pattern
        />
      </Svg>
    </View>
  );
};

export default DashedLine;
