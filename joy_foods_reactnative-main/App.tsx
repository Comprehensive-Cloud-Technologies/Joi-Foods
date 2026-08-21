import React from 'react';
import { initialWindowMetrics, SafeAreaProvider } from 'react-native-safe-area-context';
import { GestureHandlerRootView } from 'react-native-gesture-handler';
import { KeyboardProvider } from 'react-native-keyboard-controller';
import { NavigationContainer } from '@react-navigation/native';
import RouteNavigation from 'routes';
import { sty } from 'styles';
import { AppCtxProvider } from 'store';
import { ActivityLoaderProvider, FocusAwareStatusBar, SnackbarProvider } from 'components';
import { configureReanimatedLogger, ReanimatedLogLevel } from 'react-native-reanimated';

function App() {

  configureReanimatedLogger({
    level: ReanimatedLogLevel.warn,
    strict: false,
  });

  return (
    <SafeAreaProvider initialMetrics={initialWindowMetrics}>
      <GestureHandlerRootView style={sty.f1W100}>
        <KeyboardProvider>
          <NavigationContainer>
            <AppCtxProvider>
              <SnackbarProvider>
                <ActivityLoaderProvider>
                  <FocusAwareStatusBar barStyle={'dark-content'} backgroundColor={'transparent'} />
                  <RouteNavigation />
                </ActivityLoaderProvider>
              </SnackbarProvider>
            </AppCtxProvider>
          </NavigationContainer>
        </KeyboardProvider>
      </GestureHandlerRootView>
    </SafeAreaProvider>
  );
}

export default App;
