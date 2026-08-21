import * as React from 'react';
import Svg, { SvgProps, G, Path, Defs, ClipPath, Rect } from 'react-native-svg';

interface P extends SvgProps {
  props?: SvgProps;
  isChecked?: boolean;
  borderCol?: string;
}

const CheckBoxIc = ({ isChecked, borderCol, ...props }: P) =>
  isChecked ? (
    <Svg
    width={24}
    height={24}
    fill="none"
    {...props}
  >
    <Rect
      width={23}
      height={23}
      x={0.5}
      y={0.5}
      fill="#BD3839"
      stroke="#BD3839"
      rx={2.5}
    />
    <Path
      stroke="#fff"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth={1.5}
      d="m6 11.5 4 4L17.5 8"
    />
  </Svg>
  ) : (
    <Svg
    width={24}
    height={24}
    fill="none"
    {...props}
  >
    <Rect width={23} height={23} x={0.5} y={0.5} stroke="#8F8F8F" rx={2.5} />
  </Svg>
  );
export default CheckBoxIc;
