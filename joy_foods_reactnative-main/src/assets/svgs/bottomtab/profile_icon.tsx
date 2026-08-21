import * as React from "react"
import Svg, { SvgProps, Path } from "react-native-svg"

interface P extends SvgProps {
    props?: SvgProps;
    isActive?: boolean;
}

const ProfileIcon = ({isActive,...props}: P) => (
  <Svg
    width={24}
    height={24}
    fill="none"
    {...props}
  >
    <Path
      stroke={isActive?"#BD3839":"#8F8F8F"}
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth={1.5}
      d="M12 13a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z"
    />
    <Path
      stroke={isActive?"#BD3839":"#8F8F8F"}
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth={1.5}
      d="M20 21a8 8 0 0 0-16 0"
    />
  </Svg>
)
export default ProfileIcon
