import { FORWARD_IC } from "assets"
import { ReactNode } from "react"
import { StyleSheet, Text, TouchableOpacity, View } from "react-native"
import { _COL, FONT } from "utils"

const SettingsMenuItem = ({icon, title, onPress}: {icon: ReactNode, title: string, onPress: () => void}) => {
    return (
         <TouchableOpacity
        style={styles.menuItem}
        onPress={onPress}
        activeOpacity={0.8}
      >
        <View style={styles.menuLeft}>
          {icon}
          <Text allowFontScaling={false} style={styles.menuTitle}>{title}</Text>
        </View>
        <FORWARD_IC />
      </TouchableOpacity>
    )
}

export default SettingsMenuItem

const styles = StyleSheet.create({
    menuItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 16,
    borderBottomWidth: 1,
    borderBottomColor: _COL.BORDER_FOURTH,
  },
  menuLeft: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  menuTitle: {
    fontSize: 14,
    color: _COL.FINAL_BLACK,
    fontFamily: FONT.MEDIUM,
    marginLeft: 16,
    marginTop: 2,
  },
  });
