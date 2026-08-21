import React, { ComponentProps } from 'react';
import { StatusBar } from 'react-native';
import { useIsFocused } from '@react-navigation/native';

function FocusAwareStatusBar(props: ComponentProps<typeof StatusBar>) {
    const isFocused = useIsFocused();
    return isFocused ? <StatusBar translucent {...props} /> : null;
};

export default FocusAwareStatusBar;