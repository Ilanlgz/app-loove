importScripts("https://js.pusher.com/beams/service-worker.js");

// Configuration pour Loove avec tes vraies clés
const LOOVE_CONFIG = {
    instanceId: '4bbe0180-fd1d-4834-84c3-128c682c923d', // Ton vrai instance ID
    primaryKey: '07255B4D6A282E46CB5CE36FAB1F71B1CE604D2ABC9F597334F5298AF755126A', // Ta vraie clé
    defaultIcon: 'https://cdn-icons-png.flaticon.com/512/2190/2190552.png',
    defaultBadge: 'https://cdn-icons-png.flaticon.com/512/732/732200.png',
    vibration: [200, 100, 200],
    sound: '/public/sounds/notification.mp3'
};

// Personnalisation des notifications
self.addEventListener('push', function(event) {
    console.log('🔔 Push notification reçue pour Loove');
    
    const data = event.data ? event.data.json() : {};
    
    // Personnaliser selon le type de notification
    let title = data.title || '💕 Loove';
    let body = data.body || 'Nouvelle notification';
    let icon = data.icon || LOOVE_CONFIG.defaultIcon;
    let badge = data.badge || LOOVE_CONFIG.defaultBadge;
    
    // Ajouter des emojis selon le type
    if (data.type === 'message') {
        title = '💬 ' + title;
        icon = 'https://cdn-icons-png.flaticon.com/512/3193/3193015.png';
    } else if (data.type === 'like') {
        title = '❤️ ' + title;
        icon = 'https://cdn-icons-png.flaticon.com/512/2589/2589175.png';
    } else if (data.type === 'match') {
        title = '💕 ' + title;
        icon = 'https://cdn-icons-png.flaticon.com/512/833/833472.png';
    }
    
    const options = {
        body: body,
        icon: icon,
        badge: badge,
        tag: 'loove-' + (data.type || 'general'),
        data: data.data || {},
        requireInteraction: true,
        vibrate: LOOVE_CONFIG.vibration,
        actions: [
            {
                action: 'view',
                title: '👀 Voir',
                icon: '/public/images/view-icon.png'
            },
            {
                action: 'dismiss',
                title: '❌ Ignorer',
                icon: '/public/images/dismiss-icon.png'
            }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', function(event) {
    console.log('🖱️ Notification cliquée sur Loove');
    
    event.notification.close();
    
    const data = event.notification.data;
    
    // Gérer les actions personnalisées
    if (event.action === 'view' && data && data.url) {
        event.waitUntil(
            clients.openWindow(data.url)
        );
    } else if (event.action === 'dismiss') {
        console.log('❌ Notification ignorée');
        return;
    } else if (data && data.url) {
        // Clic sur la notification elle-même
        event.waitUntil(
            clients.openWindow(data.url)
        );
    } else {
        // Redirection par défaut vers la page principale de Loove
        event.waitUntil(
            clients.openWindow('http://localhost/loove/main.php')
        );
    }
});

// Gérer la fermeture des notifications
self.addEventListener('notificationclose', function(event) {
    console.log('🔕 Notification fermée');
    
    const data = event.notification.data;
    
    // Optionnel : marquer comme vue
    if (data && data.notification_id) {
        fetch('/api/notifications/mark-seen', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                notification_id: data.notification_id
            })
        });
    }
});

// Ajouter un événement pour confirmer l'activation
self.addEventListener('activate', function(event) {
    console.log('🚀 Service Worker Loove activé avec instance:', LOOVE_CONFIG.instanceId);
    console.log('🔑 Clés Pusher configurées et prêtes');
    
    // Notification de test au démarrage (optionnel)
    event.waitUntil(
        self.registration.showNotification('✅ Loove Notifications', {
            body: 'Service Worker prêt à recevoir les notifications!',
            icon: LOOVE_CONFIG.defaultIcon,
            badge: LOOVE_CONFIG.defaultBadge,
            tag: 'loove-ready',
            requireInteraction: false
        })
    );
    
    // Auto-fermer après 3 secondes
    setTimeout(() => {
        self.registration.getNotifications({ tag: 'loove-ready' })
            .then(notifications => {
                notifications.forEach(notification => notification.close());
            });
    }, 3000);
});

// Ajouter gestion d'installation
self.addEventListener('install', function(event) {
    console.log('📦 Installation Service Worker Loove');
    self.skipWaiting(); // Activer immédiatement
});
