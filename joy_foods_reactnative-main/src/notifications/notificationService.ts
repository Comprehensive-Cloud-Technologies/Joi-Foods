import { useEffect } from 'react';
import { FirebaseMessagingTypes } from '@react-native-firebase/messaging';
import notifee, { AndroidImportance, EventType } from '@notifee/react-native';
import { useNavigation } from '@react-navigation/native';
import { messaging } from 'utils/firebase';

export const useNotificationHandler = () => {
    const navigation = useNavigation<any>();

    const handleNotificationNavigation = (remoteMessage: FirebaseMessagingTypes.RemoteMessage | null) => {
        if (!remoteMessage) return;
        console.log('Navigating to Notifications from message:', JSON.stringify(remoteMessage.notification));
        // Navigation to Notifications tab in AppNavigator
        navigation.navigate('AppNavigator', { screen: 'Notifications' });
    };

    const displayNotification = async (remoteMessage: FirebaseMessagingTypes.RemoteMessage) => {
        if (!remoteMessage.notification) return;

        const channelId = await notifee.createChannel({
            id: 'default',
            name: 'Default Channel',
            importance: AndroidImportance.HIGH,
        });

        await notifee.displayNotification({
            title: remoteMessage.notification.title,
            body: remoteMessage.notification.body,
            data: remoteMessage.data,
            android: {
                channelId,
                importance: AndroidImportance.HIGH,
                pressAction: {
                    id: 'default',
                },
            },
            ios: {
                foregroundPresentationOptions: {
                    badge: true,
                    sound: true,
                    banner: true,
                    list: true,
                },
            },
        });
    };

    useEffect(() => {
        // Foreground listeners
        const unsubscribeOnMessage = messaging().onMessage(async remoteMessage => {
            console.log('A new FCM message arrived!', JSON.stringify(remoteMessage, null, 3));
            displayNotification(remoteMessage);
        });

        // Background tap listener
        const unsubscribeOnNotificationOpenedApp = messaging().onNotificationOpenedApp(remoteMessage => {
            console.log('Notification caused app to open from background state:', remoteMessage.notification);
            handleNotificationNavigation(remoteMessage);
        });

        // Killed state tap listener
        messaging()
            .getInitialNotification()
            .then(remoteMessage => {
                if (remoteMessage) {
                    console.log('Notification caused app to open from quit state:', remoteMessage.notification);
                    setTimeout(() => {
                        handleNotificationNavigation(remoteMessage);
                    }, 1000);
                }
            });

        // Notifee Foreground Event
        const unsubscribeForegroundEvent = notifee.onForegroundEvent(({ type, detail }) => {
            switch (type) {
                case EventType.PRESS:
                    console.log('User pressed notification in foreground', detail.notification);
                    handleNotificationNavigation(detail.notification as any);
                    break;
            }
        });

        return () => {
            unsubscribeOnMessage();
            unsubscribeOnNotificationOpenedApp();
            unsubscribeForegroundEvent();
        };
    }, []);
};
