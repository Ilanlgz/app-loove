<?php
// Fichier à inclure dans toutes tes pages HTML pour activer les notifications push
?>

<!-- Pusher Beams SDK -->
<script src="https://js.pusher.com/beams/2.1.0/push-notifications-cdn.js"></script>

<script>
// Configuration avec tes vraies clés Pusher Beams
const LOOVE_BEAMS_CONFIG = {
    instanceId: '4bbe0180-fd1d-4834-84c3-128c682c923d',
    currentUserId: <?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null' ?>,
    userName: '<?= isset($_SESSION['first_name']) ? addslashes($_SESSION['first_name']) : '' ?>',
    isLoggedIn: <?= isset($_SESSION['loggedin']) ? 'true' : 'false' ?>
};

// Initialiser les notifications push
document.addEventListener('DOMContentLoaded', function() {
    initLooveNotifications();
});

async function initLooveNotifications() {
    try {
        console.log('🚀 Initialisation notifications Loove...');
        
        // Vérifier le support des notifications
        if (!('Notification' in window)) {
            console.warn('❌ Notifications non supportées');
            return;
        }

        // Créer le client Pusher Beams
        const beamsClient = new PusherPushNotifications.Client({
            instanceId: LOOVE_BEAMS_CONFIG.instanceId,
        });

        // Démarrer le service
        await beamsClient.start();
        console.log('✅ Pusher Beams démarré');

        // S'abonner aux intérêts
        await beamsClient.addDeviceInterest('hello');
        
        // Si utilisateur connecté, s'abonner à ses notifications personnelles
        if (LOOVE_BEAMS_CONFIG.isLoggedIn && LOOVE_BEAMS_CONFIG.currentUserId) {
            await beamsClient.addDeviceInterest('user-' + LOOVE_BEAMS_CONFIG.currentUserId);
            console.log('✅ Abonné aux notifications pour user-' + LOOVE_BEAMS_CONFIG.currentUserId);
        }

        console.log('✅ Notifications Loove activées!');

        // Demander la permission si pas encore accordée
        if (Notification.permission === 'default') {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                showWelcomeNotification();
            }
        }

        // Stocker le client pour usage global
        window.looveBeamsClient = beamsClient;
        
    } catch (error) {
        console.error('❌ Erreur notifications Loove:', error);
    }
}

// Notification de bienvenue
function showWelcomeNotification() {
    if (Notification.permission === 'granted' && LOOVE_BEAMS_CONFIG.userName) {
        const notification = new Notification('🎉 Notifications Loove activées!', {
            body: 'Salut ' + LOOVE_BEAMS_CONFIG.userName + '! Tu recevras maintenant les notifications en temps réel 💕',
            icon: 'https://cdn-icons-png.flaticon.com/512/2190/2190552.png',
            tag: 'loove-welcome'
        });

        setTimeout(() => notification.close(), 4000);
    }
}

// Fonction de test (pour debug)
window.testLooveNotification = function() {
    if (Notification.permission === 'granted') {
        new Notification('🧪 Test Loove', {
            body: 'Notification test pour ' + LOOVE_BEAMS_CONFIG.userName,
            icon: 'https://cdn-icons-png.flaticon.com/512/2190/2190552.png'
        });
    } else {
        console.warn('❌ Permissions notifications non accordées');
    }
};

console.log('📱 Script notifications Loove chargé');
</script>

<!-- Données utilisateur pour JavaScript -->
<?php if (isset($_SESSION['user_id'])): ?>
<div id="loove-user-data" 
     data-user-id="<?= $_SESSION['user_id'] ?>" 
     data-user-name="<?= htmlspecialchars($_SESSION['first_name']) ?>"
     style="display: none;"></div>
<?php endif; ?>
