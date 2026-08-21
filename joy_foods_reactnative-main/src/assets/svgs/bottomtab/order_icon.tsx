import * as React from "react"
import Svg, { SvgProps, Path } from "react-native-svg"

interface P extends SvgProps {
    props?: SvgProps;
    isActive?: boolean;
}

const OrderIcon = ({isActive,...props}: P) =>isActive? (
  <Svg
    width={24}
    height={24}
    fill="none"
    {...props}
  >
    <Path
      stroke="#F69008"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth={1.5}
      d="m16.5 9.4-9-5.19"
    />
    <Path
      stroke="#BD3839"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth={1.5}
      d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"
    />
    <Path
      stroke="#BD3839"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth={1.5}
      d="M3.27 6.96 12 12.01l8.73-5.05M12 22.08V12"
    />
  </Svg>
):
( <Svg
    width={24}
    height={24}
    fill="none"
    {...props}
  >
    <Path
      stroke="#8F8F8F"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth={1.5}
      d="m16.5 9.4-9-5.19M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"
    />
    <Path
      stroke="#8F8F8F"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth={1.5}
      d="M3.27 6.96 12 12.01l8.73-5.05M12 22.08V12"
    />
  </Svg>)
export default OrderIcon
