<?php
namespace App\Models;

use Model;
use PDO;

class Like extends Model {
    protected $table = 'likes';

    public function __construct() {
        parent::__construct();
    }

    // Ajouter un like avec notification push
    public function addLike($user_id, $photo_id) {
        // Vérifier si le like existe déjà
        if ($this->hasLiked($user_id, $photo_id)) {
            return false;
        }

        $sql = "INSERT INTO {$this->table} (user_id, photo_id, created_at) 
                VALUES (:user_id, :photo_id, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':photo_id', $photo_id, PDO::PARAM_INT);
        
        $result = $stmt->execute();
        
        if ($result) {
            // Récupérer les infos de la photo et du propriétaire
            $photoInfo = $this->getPhotoInfo($photo_id);
            $likerInfo = $this->getUserInfo($user_id);
            
            // Ne pas notifier si c'est sa propre photo
            if ($photoInfo['user_id'] != $user_id) {
                // Envoyer la notification push
                $this->sendPushNotification($photoInfo['user_id'], [
                    'type' => 'like',
                    'title' => '❤️ Nouveau like',
                    'body' => $likerInfo['first_name'] . ' a aimé votre photo',
                    'icon' => 'https://cdn-icons-png.flaticon.com/512/2589/2589175.png',
                    'badge' => 'https://cdn-icons-png.flaticon.com/512/833/833472.png',
                    'data' => [
                        'liker_id' => $user_id,
                        'liker_name' => $likerInfo['first_name'],
                        'photo_id' => $photo_id,
                        'url' => '/loove/profile.php?id=' . $user_id
                    ]
                ]);
            }
        }
        
        return $result;
    }

    // Vérifier si l'utilisateur a déjà liké
    public function hasLiked($user_id, $photo_id) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE user_id = :user_id AND photo_id = :photo_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':photo_id', $photo_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Récupérer les infos d'une photo
    private function getPhotoInfo($photo_id) {
        $sql = "SELECT user_id, filename FROM photos WHERE id = :photo_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':photo_id', $photo_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Récupérer les infos d'un utilisateur
    private function getUserInfo($user_id) {
        $sql = "SELECT first_name, last_name, profile_picture FROM users WHERE id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Envoyer notification push (même méthode que Message)
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
                            'actions' => [
                                [
                                    'action' => 'view',
                                    'title' => '👀 Voir profil',
                                    'icon' => 'https://cdn-icons-png.flaticon.com/512/159/159604.png'
                                ]
                            ]
                        ]
                    ]
                ]
            );
            
            error_log('Notification push like envoyée à user-' . $user_id);
            return true;
            
        } catch (Exception $e) {
            error_log('Erreur notification push like: ' . $e->getMessage());
            return false;
        }
    }
}
