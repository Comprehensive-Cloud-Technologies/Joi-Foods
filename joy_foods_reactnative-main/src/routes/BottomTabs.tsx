import { EventArg } from "@react-navigation/native";
import { FontS } from "function";
import { useT } from "internationalization";
import { Pressable } from "react-native";
import Animated, { FlipInXDown } from "react-native-reanimated";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import { sty } from "styles";
import { StackProps } from "types";
import { _COL, BottomTab, BottomTabOpt, FONT } from "utils";

const { Navigator, Screen } = BottomTab;

const AnimatedLabel = ({ f, t }: { f: boolean; t: string }) => (
  <Animated.Text
    key={String(f)}
    entering={f ? FlipInXDown.springify() : undefined}
    style={f ? {
    fontSize: FontS(10),
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.TEXT_BLACK,
    textAlign: "center",
    marginTop: 5
  } : {
    fontSize: FontS(10),
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.TEXT_GREY,
    textAlign: "center",
    marginTop: 5
  }}
  >
    {t}
  </Animated.Text>
);

function BottomTabs({ navigation }: StackProps<'SplashScr'>) {
  const { t } = useT();
  const { bottom } = useSafeAreaInsets();

  // function onAddPostTab(e: EventArg<'tabPress', true, undefined>) {
  //   e.preventDefault();
  //   navigation.navigate('VideoRecordScr', { isDisplayAll: true });
  // }


  return (
    <Navigator
      screenOptions={{
        ...BottomTabOpt(bottom),
        tabBarButton: props => {
          const { ref, ...restProps } = props;
          return (
            <Pressable
              {...restProps}
              android_ripple={{ color: _COL.PRIMARY_DARK_01, borderless: true }}
              style={sty.f1CtrW100}
            />
          );
        },
      }}
    >
      {/* <Screen
        name="HomeTab"
        component={HomeTab}
        options={{
          tabBarLabel: ({ focused }) =>
            AnimatedLabel({ f: focused, t: t('HOME_LABEL') }),
          tabBarIcon: ({ focused, size }) => (
            <HOME_IC
              isActive={focused}
              width={FontS(size)}
              height={FontS(size)}
            />
          ),
        }}
      />
      <Screen
        name="ClipsTab"
        component={ClipsTab}
        options={{
          tabBarLabel: ({ focused }) => (
            <AnimatedLabel f={focused} t={t('CLIPS_LABEL')} />
          ),
          tabBarIcon: ({ focused, size }) => (
            <CLIPS_IC
              isActive={focused}
              width={FontS(size)}
              height={FontS(size)}
            />
          ),
        }}
      />
      <Screen
        name="AddClipTab"
        component={AddClipTab}
        options={{
          tabBarLabel: ({ focused }) => (
            <AnimatedLabel f={focused} t={t('ADD_CLIP_LABEL')} />
          ),
          tabBarIcon: ({ focused, size }) => (
            <ADD_CLIP_IC
              isActive={focused}
              width={FontS(size)}
              height={FontS(size)}
            />
          ),
        }}
        listeners={{ tabPress: onAddPostTab }}
      />
      <Screen
        name="MessagesTab"
        component={MessagesTab}
        options={{
          tabBarLabel: ({ focused }) => (
            <AnimatedLabel f={focused} t={t('MESSAGES_LABEL')} />
          ),
          tabBarIcon: ({ focused, size }) => (
            <MESSAGES_IC
              isActive={focused}
              width={FontS(size)}
              height={FontS(size)}
            />
          ),
        }}
      />
      <Screen
        name="ProfileTab"
        component={ProfileTab}
        options={{
          tabBarLabel: ({ focused }) => (
            <AnimatedLabel f={focused} t={t('PROFILE_LABEL')} />
          ),
          tabBarIcon: ({ focused, size }) =>
            user?.profile_image ? (
              <View
                style={{
                  borderRadius: 6,
                  borderWidth: focused ? 1.5 : 0,
                  borderColor: focused ? _COL.SECONDARY : _COL.WHITE,
                  padding: 1,
                }}
              >
                <Image
                  source={{ uri: user?.profile_image ?? '' }}
                  width={FontS(size)}
                  height={FontS(size)}
                  borderRadius={6}
                />
              </View>
            ) : (
              <PROFILE_IC
                isActive={focused}
                width={FontS(size)}
                height={FontS(size)}
              />
            ),
        }}
      /> */}
    </Navigator>
  );
}

export default BottomTabs;
