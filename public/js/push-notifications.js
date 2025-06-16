class PushNotificationManager {
    constructor() {
        this.instanceId = 'YOUR_INSTANCE_ID'; // Remplace par ton instance ID
        this.beamsClient = null;
        this.userId = null;
        
        this.init();
    }

    async init() {
        try {
            // Vérifier si les notifications sont supportées
            if (!('Notification' in window)) {
                console.warn('Ce navigateur ne supporte pas les notifications');
                return;
            }

            // Initialiser Pusher Beams
            this.beamsClient = new PusherPushNotifications.Client({
                instanceId: this.instanceId,
            });

            // Démarrer le service
            await this.beamsClient.start();
            
            // Récupérer l'ID utilisateur depuis la session/DOM
            this.userId = this.getCurrentUserId();
            
            if (this.userId) {
                // S'abonner aux notifications pour cet utilisateur
                await this.beamsClient.setUserId('user-' + this.userId, {
                    fetchToken: (userId) => {
                        return fetch('/api/pusher/beams-auth', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                user_id: userId,
                            }),
                        }).then(response => response.json());
                    }
                });

                console.log('✅ Notifications push activées pour user-' + this.userId);
            }

            // Écouter les notifications
            this.setupNotificationListeners();
            
        } catch (error) {
            console.error('Erreur initialisation notifications:', error);
        }
    }

    getCurrentUserId() {
        // Récupérer depuis un élément DOM caché ou une variable JS
        const userIdElement = document.querySelector('[data-user-id]');
        if (userIdElement) {
            return userIdElement.getAttribute('data-user-id');
        }
        
        // Ou depuis une variable globale
        return window.currentUserId || null;
    }

    setupNotificationListeners() {
        // Écouter les clics sur les notifications
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data && event.data.type === 'notification-click') {
                const notificationData = event.data.data;
                
                // Rediriger vers l'URL appropriée
                if (notificationData.url) {
                    window.location.href = notificationData.url;
                }
            }
        });
    }

    // Demander la permission pour les notifications
    async requestPermission() {
        if (Notification.permission === 'default') {
            const permission = await Notification.requestPermission();
            return permission === 'granted';
        }
        return Notification.permission === 'granted';
    }

    // Afficher une notification locale (fallback)
    showLocalNotification(title, options = {}) {
        if (Notification.permission === 'granted') {
            const notification = new Notification(title, {
                body: options.body || '',
                icon: options.icon || '/assets/images/logo.png',
                badge: options.badge || '/assets/images/badge.png',
                tag: options.tag || 'loove-notification',
                requireInteraction: true,
                ...options
            });

            notification.onclick = () => {
                if (options.url) {
                    window.location.href = options.url;
                }
                notification.close();
            };

            // Auto-fermer après 5 secondes
            setTimeout(() => {
                notification.close();
            }, 5000);
        }
    }
}

// Service Worker pour gérer les notifications en arrière-plan
const swCode = `
self.addEventListener('push', function(event) {
    const data = event.data ? event.data.json() : {};
    
    const title = data.title || 'Loove';
    const options = {
        body: data.body || 'Nouvelle notification',
        icon: data.icon || '/assets/images/logo.png',
        badge: data.badge || '/assets/images/badge.png',
        tag: 'loove-notification',
        data: data.data || {},
        actions: data.actions || [
            {
                action: 'view',
                title: 'Voir',
                icon: '/assets/images/view.png'
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    
    const data = event.notification.data;
    
    if (event.action === 'view' && data.url) {
        event.waitUntil(
            clients.openWindow(data.url)
        );
    }
});
`;

// Enregistrer le Service Worker
if ('serviceWorker' in navigator) {
    const blob = new Blob([swCode], { type: 'application/javascript' });
    const swUrl = URL.createObjectURL(blob);
    
    navigator.serviceWorker.register(swUrl)
        .then(() => {
            console.log('✅ Service Worker enregistré');
            // Initialiser les notifications après l'enregistrement
            window.pushManager = new PushNotificationManager();
        })
        .catch(error => {
            console.error('Erreur Service Worker:', error);
        });
}
