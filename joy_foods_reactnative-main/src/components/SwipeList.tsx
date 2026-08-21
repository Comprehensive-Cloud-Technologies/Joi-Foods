import { DELETE_IC } from "assets";
import { useEffect, useRef } from "react";
import { Animated, PanResponder, StyleSheet, TouchableOpacity, View } from "react-native";
import { ICartProduct } from "types";
import { _COL } from "utils/constants";

interface SwipeableMyCartProps {
    item: ICartProduct;
    onDelete: (id: number) => void;
    isSelectionMode: boolean;
    isSelected: boolean;
    onToggleSelect: (id: number) => void;
    openedItemId: number | null;
    setOpenedItemId: (id: number | null) => void;
    children: React.ReactNode;
}

const DELETE_BUTTON_WIDTH = 80;

const SwipeableMyCart: React.FC<SwipeableMyCartProps> = ({
    item,
    onDelete,
    isSelectionMode,
    isSelected,
    onToggleSelect,
    openedItemId,
    setOpenedItemId,
    children,
}) => {
    const translateX = useRef(new Animated.Value(0)).current;
    const isOpen = openedItemId === item?.cart_id;
    const CHECKBOX_WIDTH = 50;
    
    // Refs to keep track of current prop values for the PanResponder (to avoid stale closures)
    const currentSelectionModeRef = useRef(isSelectionMode);
    const isOpenRef = useRef(isOpen);
    const setOpenedItemIdRef = useRef(setOpenedItemId);
    const itemIdRef = useRef(item?.cart_id);
    const startOffset = useRef(0);

    useEffect(() => {
        currentSelectionModeRef.current = isSelectionMode;
    }, [isSelectionMode]);

    useEffect(() => {
        isOpenRef.current = isOpen;
    }, [isOpen]);

    useEffect(() => {
        setOpenedItemIdRef.current = setOpenedItemId;
    }, [setOpenedItemId]);

    useEffect(() => {
        itemIdRef.current = item?.cart_id;
    }, [item?.cart_id]);

    const panResponder = useRef(
        PanResponder.create({
            onStartShouldSetPanResponder: () => false,
            onMoveShouldSetPanResponder: (_, gestureState) => {
                if (currentSelectionModeRef.current) return false;
                // More sensitive move detection (5px instead of 10px)
                return (
                    Math.abs(gestureState.dx) > 5 &&
                    Math.abs(gestureState.dy) < Math.abs(gestureState.dx)
                );
            },
            onPanResponderGrant: () => {
                // Determine starting point based on current open state
                startOffset.current = isOpenRef.current ? -DELETE_BUTTON_WIDTH : 0;
            },
            onPanResponderMove: (_, gestureState) => {
                let newX = startOffset.current + gestureState.dx;
                // Clamp movement
                if (newX > 0) newX = 0;
                if (newX < -DELETE_BUTTON_WIDTH) newX = -DELETE_BUTTON_WIDTH;
                translateX.setValue(newX);
            },
            onPanResponderRelease: (_, gestureState) => {
                const currentX = startOffset.current + gestureState.dx;
                const flickThreshold = -0.3; // More sensitive flick
                
                // Lower threshold for opening (25% of width instead of 50%)
                const openThreshold = -DELETE_BUTTON_WIDTH * 0.25;
                const shouldBeOpen = currentX < openThreshold || gestureState.vx < flickThreshold;

                const springConfig = {
                    useNativeDriver: true,
                    friction: 7,
                    tension: 50,
                };

                if (shouldBeOpen) {
                    // Snap Open
                    Animated.spring(translateX, {
                        toValue: -DELETE_BUTTON_WIDTH,
                        ...springConfig,
                    }).start();
                    setOpenedItemIdRef.current(itemIdRef.current ?? 0);
                } else {
                    // Snap Closed
                    Animated.spring(translateX, {
                        toValue: 0,
                        ...springConfig,
                    }).start();
                    setOpenedItemIdRef.current(null);
                }
            },
            onPanResponderTerminate: () => {
                const springConfig = {
                    useNativeDriver: true,
                    friction: 7,
                    tension: 50,
                };
                // If gesture is interrupted, return to the state it was in before the gesture started
                Animated.spring(translateX, {
                    toValue: isOpenRef.current ? -DELETE_BUTTON_WIDTH : 0,
                    ...springConfig,
                }).start();
            },
        }),
    ).current;

    // Handle external changes to openedItemId (e.g., closing another item)
    useEffect(() => {
        if (openedItemId !== item.cart_id) {
            Animated.spring(translateX, {
                toValue: 0,
                useNativeDriver: true,
                friction: 8,
                tension: 40,
            }).start();
        }
    }, [openedItemId]);

    // Handle selection mode changes
    useEffect(() => {
        if (isSelectionMode) {
            Animated.spring(translateX, {
                toValue: CHECKBOX_WIDTH,
                useNativeDriver: true,
            }).start();
            // Don't call setOpenedItemId here to avoid unnecessary state updates in parent
        } else {
            // When exiting selection mode, return to 0
            Animated.spring(translateX, {
                toValue: 0,
                useNativeDriver: true,
            }).start();
        }
    }, [isSelectionMode]);

    return (
        <View style={styles.swipeContainer}>
            {!isSelectionMode && (
                <View style={styles.deleteButtonContainer}>
                    <TouchableOpacity 
                        onPress={() => onDelete(item.cart_id ?? 0)} 
                        activeOpacity={0.8} 
                        style={styles.deleteButton}
                    >
                        <DELETE_IC />
                    </TouchableOpacity>
                </View>
            )}
            <Animated.View
                style={[{ transform: [{ translateX }] }]}
                {...panResponder.panHandlers}
            >
                {children}
            </Animated.View>
        </View>
    );
};

export default SwipeableMyCart;

const styles = StyleSheet.create({
    swipeContainer: {      
       backgroundColor: _COL.WHITE,
       overflow: 'hidden',
    },
    deleteButtonContainer: {
        position: 'absolute',
        right: 0,
        top: 0,
        bottom: 0,
        width: DELETE_BUTTON_WIDTH,
        backgroundColor: _COL.BORDER_SEVENTH,
    },
    deleteButton: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
    },
});