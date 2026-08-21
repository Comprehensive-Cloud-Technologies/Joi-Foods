import * as React from "react"
import { ColorValue } from "react-native";
import Svg, { SvgProps, G, Path, Defs, ClipPath } from "react-native-svg"

interface P extends SvgProps{
    props?:SvgProps;
    color?:ColorValue;
}

const CreditDebitCard = ({color, ...props}: P) => (
<Svg
    width={25}
    height={25}
    fill="none"
    {...props}
  >
    <G
      stroke={color || "#BD3839"}
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth={1.5}
      clipPath="url(#a)"
    >
      <Path d="m7.104 17.104 9.939-10.061M8.043 7.098l9-.055.055 9" />
    </G>
    <Defs>
      <ClipPath id="a">
        <Path fill="#fff" d="M.147 24.146 0 .146 24 0l.147 24z" />
      </ClipPath>
    </Defs>
  </Svg>
)
export default CreditDebitCard
