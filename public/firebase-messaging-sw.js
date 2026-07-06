// Give the service worker access to Firebase Messaging.
// Note that you can only use Firebase Messaging here. Other Firebase libraries
// are not available in the service worker.importScripts('https://www.gstatic.com/firebasejs/7.23.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/9.2.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.2.0/firebase-messaging-compat.js');
/*
Initialize the Firebase app in the service worker by passing in the messagingSenderId.
*/
firebase.initializeApp({
    apiKey: "AIzaSyBw7IAPcSpu2i0lqkMeM0R2sd8JrNn3S_k",
    authDomain: "mrerp-arp.firebaseapp.com",
    projectId: "mrerp-arp",
    storageBucket: "mrerp-arp.appspot.com",
    messagingSenderId: "554317468175",
    appId: "1:554317468175:web:2a87449e43caba5f3ec97a",
    databaseURL: 'https://mrerp-arp-default-rtdb.asia-southeast1.firebasedatabase.app/',
});

// Retrieve an instance of Firebase Messaging so that it can handle background
// messages.
const messaging = firebase.messaging();
messaging.onBackgroundMessage(function (payload) {
    const title = payload.notification.title;
    const options = {
        body: payload.notification.body,
        icon: '/firebase-logo.png',
    };

    self.registration.showNotification(title,
        options);
});