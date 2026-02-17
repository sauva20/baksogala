importScripts(
    "https://www.gstatic.com/firebasejs/10.9.0/firebase-app-compat.js",
);
importScripts(
    "https://www.gstatic.com/firebasejs/10.9.0/firebase-messaging-compat.js",
);

firebase.initializeApp({
    // PERBAIKAN: API Key harus lengkap (AIzaSyDmAom7VDb0...)
    apiKey: "AIzaSyDmAom7VDb0OkTijt0Hf5UE3YB1kuNvywA",
    authDomain: "pondasikita-465612.firebaseapp.com",
    projectId: "pondasikita-465612",
    storageBucket: "pondasikita-465612.firebasestorage.app",
    messagingSenderId: "92626258010",
    appId: "1:92626258010:web:35b5aedc63783dd6387063",
    measurementId: "G-GWR362C6NP",
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function (payload) {
    console.log("Notifikasi Background Masuk:", payload);

    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: "/assets/images/GALA.png",
    };

    return self.registration.showNotification(
        notificationTitle,
        notificationOptions,
    );
});
