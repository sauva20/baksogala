// 1. Import SDK Firebase Compat (Gunakan versi yang konsisten)
importScripts(
    "https://www.gstatic.com/firebasejs/10.9.0/firebase-app-compat.js",
);
importScripts(
    "https://www.gstatic.com/firebasejs/10.9.0/firebase-messaging-compat.js",
);

// 2. Inisialisasi Firebase di Service Worker
firebase.initializeApp({
    apiKey: "AIzaSyDmAom7VDb0OkTijt0Hf5UE3YB1kuNvywA",
    authDomain: "pondasikita-465612.firebaseapp.com",
    projectId: "pondasikita-465612",
    storageBucket: "pondasikita-465612.firebasestorage.app",
    messagingSenderId: "92626258010",
    appId: "1:92626258010:web:35b5aedc63783dd6387063",
    measurementId: "G-GWR362C6NP",
});

const messaging = firebase.messaging();

/**
 * 3. Handler Notifikasi saat Browser di Background (Lagi buka IG/Pindah Tab)
 * Bagian ini yang membuat banner notifikasi sistem muncul di HP/Windows.
 */
messaging.onBackgroundMessage(function (payload) {
    console.log("Notifikasi Background Masuk:", payload);

    // Ambil data dari objek 'notification' atau objek 'data' (sebagai cadangan/fallback)
    const notificationTitle =
        payload.notification?.title || payload.data?.title || "Pesanan Baru!";
    const notificationOptions = {
        body:
            payload.notification?.body ||
            payload.data?.body ||
            "Cek aplikasi admin Bakso Gala sekarang.",
        icon: "/assets/images/GALA.png", // Pastikan path logo benar
        badge: "/assets/images/GALA.png", // Ikon kecil di status bar HP
        tag: "new-order-alert", // Biar notif yang sama tidak menumpuk berantakan
        requireInteraction: true, // Notif tidak hilang sampai ditutup user
        vibrate: [200, 100, 200], // Pola getar untuk HP
    };

    return self.registration.showNotification(
        notificationTitle,
        notificationOptions,
    );
});

/**
 * 4. Handler Klik Notifikasi
 * Biar pas user klik banner notif-nya, langsung otomatis buka tab Admin Orders.
 */
self.addEventListener("notificationclick", function (event) {
    event.notification.close(); // Tutup banner notifnya

    event.waitUntil(
        clients
            .matchAll({ type: "window", includeUncontrolled: true })
            .then(function (clientList) {
                // Jika tab admin sudah terbuka, fokuskan ke tab tersebut
                for (let i = 0; i < clientList.length; i++) {
                    let client = clientList[i];
                    if (
                        client.url.includes("/admin/orders") &&
                        "focus" in client
                    ) {
                        return client.focus();
                    }
                }
                // Jika belum terbuka, buka tab baru ke halaman pesanan
                if (clients.openWindow) {
                    return clients.openWindow("/admin/orders");
                }
            }),
    );
});
