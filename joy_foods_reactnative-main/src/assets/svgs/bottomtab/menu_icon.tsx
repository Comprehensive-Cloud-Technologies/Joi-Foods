import * as React from "react"
import Svg, { SvgProps, Path } from "react-native-svg"

interface P extends SvgProps {
    props?: SvgProps;
    isActive?: boolean;
}

const MenuIcon = ({isActive,...props}: P) => isActive?(
  <Svg
    width={24}
    height={24}
    fill="none"
    {...props}
  >
    <Path
      stroke="#E63946"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth={1.5}
      d="M3.75 6.75h16.5m-16.5 10.5h16.5"
    />
    <Path
      stroke="#F69008"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth={1.5}
      d="M3.75 12h16.5"
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
      d="M3.75 6.75h16.5m-16.5 10.5h16.5M3.75 12h16.5"
    />
  </Svg>
)

export default MenuIcon
