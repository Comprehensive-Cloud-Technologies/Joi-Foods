import * as React from "react"
import Svg, { SvgProps, Path } from "react-native-svg"

interface P extends SvgProps {
    props?: SvgProps;
    isActive?: boolean;
}

const NotificationIcon = ({isActive,...props}: P) => isActive? (
  <Svg
    width={24}
    height={24}
    fill="none"
    {...props}
  >
    <Path
      stroke="#BD3839"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth={1.5}
      d="M10.268 21a2 2 0 0 0 3.464 0"
    />
    <Path
      stroke="#F69008"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth={1.5}
      d="M22 8c0-2.3-.8-4.3-2-6"
    />
    <Path
      stroke="#BD3839"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth={1.5}
      d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 1 0 6 8c0 4.499-1.411 5.956-2.738 7.326Z"
    />
    <Path
      stroke="#F69008"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth={1.5}
      d="M4 2C2.8 3.7 2 5.7 2 8"
    />
  </Svg>
):(
    <Svg
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
      d="M10.268 21a2 2 0 0 0 3.464 0M22 8c0-2.3-.8-4.3-2-6M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326ZM4 2C2.8 3.7 2 5.7 2 8"
    />
  </Svg>
)
export default NotificationIcon
