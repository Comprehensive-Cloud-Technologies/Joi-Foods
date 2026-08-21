import * as React from "react"
import Svg, { SvgProps, G, Path, Defs, ClipPath } from "react-native-svg"

interface P extends SvgProps {
 props?:SvgProps;
 isOpen?:boolean;   
}

const EyeIcon = ({isOpen, ...props}: P) => 
   isOpen?(
     <Svg
    width={24}
    height={24}
    fill="none"
    {...props}
  >
    <G
      stroke="#8F8F8F"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth={2}
      clipPath="url(#a)"
    >
      <Path d="M10 12a2 2 0 1 0 4 0 2 2 0 0 0-4 0Z" />
      <Path d="M21 12c-2.4 4-5.4 6-9 6-3.6 0-6.6-2-9-6 2.4-4 5.4-6 9-6 3.6 0 6.6 2 9 6Z" />
    </G>
    <Defs>
      <ClipPath id="a">
        <Path fill="#fff" d="M0 0h24v24H0z" />
      </ClipPath>
    </Defs>
  </Svg>
   ) :(
  <Svg
    width={24}
    height={24}
    fill="none"
    {...props}
  >
    <G
      stroke="#8F8F8F"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth={2}
      clipPath="url(#a)"
      opacity={0.6}
    >
      <Path d="M21 9c-2.4 2.667-5.4 4-9 4-3.6 0-6.6-1.333-9-4M3 15l2.5-3.8M21 14.976 18.508 11.2M9 17l.5-4M15 17l-.5-4" />
    </G>
    <Defs>
      <ClipPath id="a">
        <Path fill="#fff" d="M0 0h24v24H0z" />
      </ClipPath>
    </Defs>
  </Svg>
)
export default EyeIcon
