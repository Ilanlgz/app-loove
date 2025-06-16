<?php
namespace App\Models;

use Model;
use PDO;

class Match extends Model {
    protected $table = 'matches';

    public function __construct() {
        parent::__construct();
    }

    // Créer un match avec notification push
    public function createMatch($user1_id, $user2_id) {
        // Vérifier si le match existe déjà
        if ($this->matchExists($user1_id, $user2_id)) {
            return false;
        }

        $sql = "INSERT INTO {$this->table} (user1_id, user2_id, created_at) 
                VALUES (:user1_id, :user2_id, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user1_id', $user1_id, PDO::PARAM_INT);
        $stmt->bindParam(':user2_id', $user2_id, PDO::PARAM_INT);
        
        $result = $stmt->execute();
        
        if ($result) {
            // Récupérer les infos des deux utilisateurs
            $user1Info = $this->getUserInfo($user1_id);
            $user2Info = $this->getUserInfo($user2_id);
            
            // Notifier user1
            $this->sendPushNotification($user1_id, [
                'type' => 'match',
                'title' => '💕 Nouveau match!',
                'body' => 'Vous avez un match avec ' . $user2Info['first_name'] . '!',
                'icon' => 'https://cdn-icons-png.flaticon.com/512/833/833472.png',
                'badge' => 'https://cdn-icons-png.flaticon.com/512/732/732200.png',
                'data' => [
                    'match_id' => $user2_id,
                    'match_name' => $user2Info['first_name'],
                    'url' => '/loove/matches.php'
                ]
            ]);
            
            // Notifier user2
            $this->sendPushNotification($user2_id, [
                'type' => 'match',
                'title' => '💕 Nouveau match!',
                'body' => 'Vous avez un match avec ' . $user1Info['first_name'] . '!',
                'icon' => 'https://cdn-icons-png.flaticon.com/512/833/833472.png',
                'badge' => 'https://cdn-icons-png.flaticon.com/512/732/732200.png',
                'data' => [
                    'match_id' => $user1_id,
                    'match_name' => $user1Info['first_name'],
                    'url' => '/loove/matches.php'
                ]
            ]);
        }
        
        return $result;
    }

    // Vérifier si un match existe
    public function matchExists($user1_id, $user2_id) {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE (user1_id = :user1_id AND user2_id = :user2_id) 
                   OR (user1_id = :user2_id AND user2_id = :user1_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user1_id', $user1_id, PDO::PARAM_INT);
        $stmt->bindParam(':user2_id', $user2_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Récupérer les infos d'un utilisateur
    private function getUserInfo($user_id) {
        $sql = "SELECT first_name, last_name, profile_picture FROM users WHERE id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Envoyer notification push
    private function sendPushNotification($user_id, $notification_data) {
        try {
            require_once __DIR__ . '/../../vendor/autoload.php';
            
            $beamsClient = new \Pusher\PushNotifications\PushNotifications([
                'instanceId' => '4bbe0180-fd1d-4834-84c3-128c682c923d',
                'secretKey' => '07255B4D6A282E46CB5CE36FAB1F71B1CE604D2ABC9F597334F5298AF755126A',
            ]);

            $publishResponse = $beamsClient->publishToInterests(
                ['user-' . $user_id],
                [
                    'web' => [
                        'notification' => [
                            'title' => $notification_data['title'],
                            'body' => $notification_data['body'],
                            'icon' => $notification_data['icon'],
                            'badge' => $notification_data['badge'],
                            'data' => $notification_data['data'],
                            'requireInteraction' => true,
                            'vibrate' => [200, 100, 200, 100, 200],
                            'actions' => [
                                [
                                    'action' => 'view',
                                    'title' => '💕 Voir matches',
                                    'icon' => 'https://cdn-icons-png.flaticon.com/512/833/833472.png'
                                ]
                            ]
                        ]
                    ]
                ]
            );
            
            error_log('Notification push match envoyée à user-' . $user_id);
            return true;
            
        } catch (Exception $e) {
            error_log('Erreur notification push match: ' . $e->getMessage());
            return false;
        }
    }
}
