import React, { useEffect, useRef, useState } from 'react';
import { View, Text, TouchableOpacity, Animated, FlatList, StyleSheet, Pressable, Modal } from 'react-native';
import { _COL, FONT } from 'utils';
import { DOWN_IC } from 'assets';

interface DropdownProps {
  label: string;
  placeholder: string;
  value?: string;
  data: any[];
  visible: boolean;
  onToggle: () => void;
  onSelect: (item: any) => void;
  onClose: () => void;
  zIndex?: number;
}

const Dropdown: React.FC<DropdownProps> = ({
  label,
  placeholder,
  value,
  data,
  visible,
  onToggle,
  onSelect,
  onClose,
  zIndex = 1,
}) => {
  const animatedHeight = useRef(new Animated.Value(0)).current;
  const animatedOpacity = useRef(new Animated.Value(0)).current;
  const containerRef = useRef<View>(null);
  const [coords, setCoords] = useState({ x: 0, y: 0, width: 0, height: 0 });

  useEffect(() => {
    if (visible) {
      containerRef.current?.measureInWindow((x, y, width, height) => {
        setCoords({ x, y, width, height });
      });
      Animated.parallel([
        Animated.timing(animatedHeight, {
          toValue: 1,
          duration: 300,
          useNativeDriver: false,
        }),
        Animated.timing(animatedOpacity, {
          toValue: 1,
          duration: 300,
          useNativeDriver: false,
        }),
      ]).start();
    } else {
      Animated.parallel([
        Animated.timing(animatedHeight, {
          toValue: 0,
          duration: 300,
          useNativeDriver: false,
        }),
        Animated.timing(animatedOpacity, {
          toValue: 0,
          duration: 300,
          useNativeDriver: false,
        }),
      ]).start();
    }
  }, [visible]);

  const maxHeight = animatedHeight.interpolate({
    inputRange: [0, 1],
    outputRange: [0, 200],
  });

  return (
    <View style={[styles.mainContainer, { zIndex }]} ref={containerRef}>
      <Text allowFontScaling={false} style={styles.label}>{label}</Text>

      <TouchableOpacity
        style={styles.dropdownContainer}
        onPress={onToggle}
        activeOpacity={0.7}
      >
        <Text
          allowFontScaling={false}
          style={value ? styles.selectedText : styles.placeholderText}
        >
          {value || placeholder}
        </Text>
        <DOWN_IC />
      </TouchableOpacity>

      <Modal
        visible={visible}
        transparent={true}
        animationType="none"
        onRequestClose={onClose}
      >
        <Pressable style={styles.modalOverlay} onPress={onClose}>
          <Animated.View
            style={[
              styles.listContainer,
              {
                top: coords.y + coords.height + 4,
                left: coords.x,
                width: coords.width,
                maxHeight: maxHeight,
                opacity: animatedOpacity,
              },
            ]}
          >
            <FlatList
              data={data}
              keyExtractor={(item, index) => item.id?.toString() ?? index.toString()}
              bounces={false}
              renderItem={({ item }) => (
                <TouchableOpacity
                  onPress={() => {
                    onSelect(item);
                    onClose();
                  }}
                  style={styles.itemTouch}
                >
                  <Text allowFontScaling={false} style={styles.itemText}>
                    {item.name}
                  </Text>
                </TouchableOpacity>
              )}
            />
          </Animated.View>
        </Pressable>
      </Modal>
    </View>
  );
};

export default Dropdown;

const styles = StyleSheet.create({
  mainContainer: {
    marginTop: 16,
    width: '100%',
  },
  label: {
    fontSize: 18,
    fontFamily: FONT.SEMI_BOLD,
    color: _COL.FINAL_BLACK,
    marginBottom: 9,
  },
  dropdownContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingLeft: 15,
    paddingRight: 13,
    borderWidth: 1.5,
    borderColor: _COL.BORDER,
    borderRadius: 12,
    paddingVertical: 12,
    backgroundColor: _COL.WHITE,
  },
  selectedText: {
    fontSize: 14,
    color: _COL.FINAL_BLACK,
    fontFamily: FONT.REGULAR,
  },
  placeholderText: {
    fontSize: 14,
    color: _COL.TEXT_GREY_LIGHT,
    fontFamily: FONT.REGULAR,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'transparent',
  },
  listContainer: {
    backgroundColor: _COL.WHITE,
    borderWidth: 1.5,
    borderColor: _COL.BORDER,
    borderRadius: 12,
    position: 'absolute',
    elevation: 5,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    overflow: 'hidden',
  },
  itemTouch: {
    borderBottomWidth: 1,
    borderBottomColor: _COL.BORDER,
  },
  itemText: {
    padding: 12,
    fontSize: 14,
    color: _COL.FINAL_BLACK,
    fontFamily: FONT.REGULAR,
  },
});
