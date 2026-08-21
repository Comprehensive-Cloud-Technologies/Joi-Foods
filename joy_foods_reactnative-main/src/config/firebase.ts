import { initializeApp } from '@react-native-firebase/app';

const firebaseConfig = {
    projectId: "joi-foods",
    messagingSenderId: "219044014010",
    appId: "1:219044014010:android:d28a99a09be70e4f3e9f83",
};

const app = initializeApp(firebaseConfig);

export default app;