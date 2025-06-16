class LooveBeamsClient {
    constructor() {
        this.instanceId = 'a4e6f07d-67c8-4327-9be8-example'; // Remplace par ton instance ID
        this.beamsClient = null;
        this.currentUserId = null;
        
        this.init();
    }

    async init() {
        try {
            // Vérifier le support des notifications
            if (!('Notification' in window)) {
                console.warn('❌ Notifications non supportées');
                return;
            }

            // Vérifier le service worker
            if (!('serviceWorker' in navigator)) {
                console.warn('❌ Service Worker non supporté');
                return;
            }

            // Initialiser le client Beams
            this.beamsClient = new PusherPushNotifications.Client({
                instanceId: this.instanceId,
            });

            // Démarrer le service
            await this.beamsClient.start();
            console.log('✅ Pusher Beams démarré');

            // Récupérer l'ID utilisateur
            this.currentUserId = this.getCurrentUserId();
            
            if (this.currentUserId) {
                await this.authenticateUser();
            }

            // Demander la permission
            await this.requestNotificationPermission();
            
        } catch (error) {
            console.error('❌ Erreur Beams:', error);
        }
    }

    getCurrentUserId() {
        // Récupérer depuis un attribut data ou variable globale
        const userIdElement = document.querySelector('[data-user-id]');
        if (userIdElement) {
            return userIdElement.getAttribute('data-user-id');
        }
        
        // Ou depuis une variable JavaScript globale
        return window.currentUserId || document.body.dataset.userId || null;
    }

    async authenticateUser() {
        try {
            await this.beamsClient.setUserId('user-' + this.currentUserId, {
                fetchToken: (userId) => {
                    return fetch('/api/pusher/beams-auth', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            user_id: userId,
                        }),
                    }).then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur authentification');
                        }
                        return response.json();
                    });
                }
            });
            
            console.log('✅ Utilisateur authentifié:', 'user-' + this.currentUserId);
            
        } catch (error) {
            console.error('❌ Erreur authentification:', error);
        }
    }

    async requestNotificationPermission() {
        if (Notification.permission === 'default') {
            const permission = await Notification.requestPermission();
            
            if (permission === 'granted') {
                console.log('✅ Permission notifications accordée');
                this.showWelcomeNotification();
            } else {
                console.warn('❌ Permission notifications refusée');
            }
        } else if (Notification.permission === 'granted') {
            console.log('✅ Notifications déjà autorisées');
        }
    }

    showWelcomeNotification() {
        if (Notification.permission === 'granted') {
            const notification = new Notification('🎉 Notifications activées!', {
                body: 'Vous recevrez désormais les notifications de Loove',
                icon: 'https://cdn-icons-png.flaticon.com/512/2190/2190552.png',
                tag: 'welcome'
            });

            setTimeout(() => {
                notification.close();
            }, 3000);
        }
    }

    // Méthode pour tester les notifications
    testNotification() {
        if (this.beamsClient && this.currentUserId) {
            console.log('🧪 Test notification pour user-' + this.currentUserId);
            
            // Simulation d'une notification locale
            this.showLocalNotification('🧪 Test', {
                body: 'Ceci est un test de notification',
                icon: 'https://cdn-icons-png.flaticon.com/512/2190/2190552.png'
            });
        }
    }

    showLocalNotification(title, options = {}) {
        if (Notification.permission === 'granted') {
            new Notification(title, {
                body: options.body || '',
                icon: options.icon || '/assets/images/logo.png',
                tag: options.tag || 'loove-local',
                ...options
            });
        }
    }
}

// Initialiser automatiquement quand le DOM est prêt
document.addEventListener('DOMContentLoaded', () => {
    // Attendre que le SDK Pusher soit chargé
    if (typeof PusherPushNotifications !== 'undefined') {
        window.looveBeams = new LooveBeamsClient();
    } else {
        console.warn('❌ SDK Pusher Beams non chargé');
    }
});

// Debug - Exposer pour les tests
window.testBeams = () => {
    if (window.looveBeams) {
        window.looveBeams.testNotification();
    }
};
