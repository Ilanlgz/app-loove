<?php
// Fichier à inclure dans toutes les pages pour les notifications push Loove
?>

<!-- Pusher Beams SDK - Version 2.1.0 -->
<script src="https://js.pusher.com/beams/2.1.0/push-notifications-cdn.js"></script>

<!-- Configuration des notifications pour Loove avec tes vraies clés -->
<script>
// Configuration globale avec tes vraies clés Pusher
window.LOOVE_PUSH_CONFIG = {
    instanceId: '4bbe0180-fd1d-4834-84c3-128c682c923d',
    currentUserId: <?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null' ?>,
    userName: '<?= isset($_SESSION['first_name']) ? addslashes($_SESSION['first_name']) : '' ?>',
    isLoggedIn: <?= isset($_SESSION['loggedin']) ? 'true' : 'false' ?>
};

// Initialisation automatique des notifications push
document.addEventListener('DOMContentLoaded', function() {
    if (window.LOOVE_PUSH_CONFIG.isLoggedIn && window.LOOVE_PUSH_CONFIG.currentUserId) {
        initLoovePushNotifications();
    } else {
        console.log('👤 Utilisateur non connecté - Notifications désactivées');
    }
});

async function initLoovePushNotifications() {
    try {
        console.log('🚀 Initialisation notifications Loove avec instance:', window.LOOVE_PUSH_CONFIG.instanceId);
        
        // Vérifier le support
        if (!('Notification' in window) || !('serviceWorker' in navigator)) {
            console.warn('❌ Notifications non supportées sur ce navigateur');
            return;
        }

        // Initialiser Pusher Beams avec tes vraies clés
        const beamsClient = new PusherPushNotifications.Client({
            instanceId: window.LOOVE_PUSH_CONFIG.instanceId,
        });

        // Démarrer le service
        await beamsClient.start();
        console.log('✅ Pusher Beams démarré avec succès');

        // S'abonner aux intérêts généraux
        await beamsClient.addDeviceInterest('loove-general');
        await beamsClient.addDeviceInterest('user-' + window.LOOVE_PUSH_CONFIG.currentUserId);
        console.log('✅ Abonné aux notifications pour user-' + window.LOOVE_PUSH_CONFIG.currentUserId);

        // Demander la permission pour les notifications
        if (Notification.permission === 'default') {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                showWelcomeNotification();
                console.log('✅ Permission notifications accordée');
            } else {
                console.warn('❌ Permission notifications refusée');
            }
        } else if (Notification.permission === 'granted') {
            console.log('✅ Notifications déjà autorisées');
        }

        // Stocker le client globalement pour utilisation
        window.looveBeamsClient = beamsClient;
        
        // Test de fonctionnement
        console.log('🎯 Client Beams prêt - Utilisez testLooveNotification() pour tester');
        
    } catch (error) {
        console.error('❌ Erreur notifications Loove:', error);
    }
}

function showWelcomeNotification() {
    if (Notification.permission === 'granted') {
        const notification = new Notification('🎉 Notifications Loove activées!', {
            body: 'Bonjour ' + window.LOOVE_PUSH_CONFIG.userName + '! Vous recevrez maintenant les notifications en temps réel 💕',
            icon: 'https://cdn-icons-png.flaticon.com/512/2190/2190552.png',
            tag: 'loove-welcome',
            requireInteraction: false
        });

        // Fermer automatiquement après 5 secondes
        setTimeout(() => {
            notification.close();
        }, 5000);
    }
}

// Fonction pour tester les notifications (debug)
window.testLooveNotification = function() {
    console.log('🧪 Test de notification Loove');
    
    if (Notification.permission === 'granted') {
        const testNotification = new Notification('🧪 Test Loove', {
            body: 'Ceci est un test de notification pour ' + window.LOOVE_PUSH_CONFIG.userName,
            icon: 'https://cdn-icons-png.flaticon.com/512/2190/2190552.png',
            tag: 'loove-test',
            vibrate: [200, 100, 200]
        });
        
        setTimeout(() => {
            testNotification.close();
        }, 4000);
    } else {
        console.warn('❌ Permissions notifications non accordées');
    }
};

// Fonction pour envoyer une notification test via l'API
window.sendTestPushNotification = function() {
    if (!window.LOOVE_PUSH_CONFIG.currentUserId) {
        console.warn('❌ Utilisateur non connecté');
        return;
    }
    
    fetch('/api/pusher/send-test-notification.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            user_id: window.LOOVE_PUSH_CONFIG.currentUserId,
            message: 'Test depuis le navigateur!'
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('📤 Notification test envoyée:', data);
    })
    .catch(error => {
        console.error('❌ Erreur envoi test:', error);
    });
};
</script>

<!-- Ajouter l'ID utilisateur dans le DOM pour JS -->
<?php if (isset($_SESSION['user_id'])): ?>
<div id="user-data" 
     data-user-id="<?= $_SESSION['user_id'] ?>" 
     data-user-name="<?= htmlspecialchars($_SESSION['first_name']) ?>"
     data-instance-id="4bbe0180-fd1d-4834-84c3-128c682c923d"
     style="display: none;"></div>
<?php endif; ?>
