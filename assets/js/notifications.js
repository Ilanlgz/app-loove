/**
 * Loove - Module de notifications push
 * Utilise Pusher Beams pour gérer les notifications
 */

// Initialiser automatiquement les notifications 
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initPushNotifications, 500);
});

// Fonction d'initialisation des notifications push
async function initPushNotifications() {
    try {
        console.log('🚀 Initialisation des notifications push...');
        
        if (typeof PusherPushNotifications === 'undefined') {
            console.error('❌ SDK Pusher non chargé');
            return;
        }
        
        const beamsClient = new PusherPushNotifications.Client({
            instanceId: '4bbe0180-fd1d-4834-84c3-128c682c923d',
        });
        
        await beamsClient.start();
        
        // Obtenir l'ID utilisateur depuis la page
        const userId = getUserId();
        
        if (userId) {
            await beamsClient.addDeviceInterest('hello');
            await beamsClient.addDeviceInterest(`user-${userId}`);
            
            console.log(`✅ Notifications push configurées pour user-${userId}`);
            
            // Demander la permission si nécessaire
            if (Notification.permission === 'default') {
                const permission = await Notification.requestPermission();
                console.log(`📣 Permission notifications: ${permission}`);
            }
        }
    } catch (error) {
        console.error('❌ Erreur d\'initialisation:', error);
    }
}

// Fonction de test des notifications Pusher
function testPusher() {
    console.log('🧪 Test Pusher...');
    
    if (typeof PusherPushNotifications === 'undefined') {
        console.error('❌ SDK Pusher non chargé');
        return;
    }
    
    const userId = getUserId();
    if (!userId) {
        console.error('❌ ID utilisateur non trouvé');
        return;
    }
    
    const beamsClient = new PusherPushNotifications.Client({
        instanceId: '4bbe0180-fd1d-4834-84c3-128c682c923d',
    });
    
    beamsClient.start()
        .then(() => beamsClient.addDeviceInterest('hello'))
        .then(() => beamsClient.addDeviceInterest(`user-${userId}`))
        .then(() => {
            console.log('✅ Pusher configuré!');
            console.log('📤 Commande cURL pour tester:');
            console.log(`curl -H "Content-Type: application/json" -H "Authorization: Bearer 07255B4D6A282E46CB5CE36FAB1F71B1CE604D2ABC9F597334F5298AF755126A" -X POST "https://4bbe0180-fd1d-4834-84c3-128c682c923d.pushnotifications.pusher.com/publish_api/v1/instances/4bbe0180-fd1d-4834-84c3-128c682c923d/publishes" -d '{"interests":["user-${userId}"],"web":{"notification":{"title":"💕 Test notification","body":"Message depuis cURL!"}}}'`);
        })
        .catch(console.error);
}

// Fonction simple pour récupérer l'ID utilisateur
function getUserId() {
    // Méthode 1: Chercher dans les données utilisateur
    const userInfoElement = document.querySelector('[data-user-id]');
    if (userInfoElement && userInfoElement.dataset.userId) {
        return userInfoElement.dataset.userId;
    }
    
    // Méthode 2: Extraire d'un élément HTML
    const userInfoText = document.querySelector('.user-info-section')?.textContent;
    if (userInfoText) {
        const match = userInfoText.match(/ID\s*:\s*(\d+)/i);
        if (match && match[1]) {
            return match[1];
        }
    }
    
    // Fallback
    return null;
}

// Exposer les fonctions au scope global
window.testPusher = testPusher;
window.initPushNotifications = initPushNotifications;

console.log('✅ Module notifications push chargé');
