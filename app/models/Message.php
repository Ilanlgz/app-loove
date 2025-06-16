<?php
namespace App\Models;

use Model;
use PDO;

class Message extends Model {
    protected $table = 'messages';

    public function __construct() {
        parent::__construct();
    }

    // Envoyer un message avec notification push
    public function sendMessage($sender_id, $receiver_id, $content) {
        $sql = "INSERT INTO {$this->table} (sender_id, receiver_id, content, sent_at) 
                VALUES (:sender_id, :receiver_id, :content, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
        $stmt->bindParam(':receiver_id', $receiver_id, PDO::PARAM_INT);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        
        $result = $stmt->execute();
        
        if ($result) {
            // Récupérer les infos de l'expéditeur
            $senderInfo = $this->getUserInfo($sender_id);
            
            // Envoyer la notification push automatiquement
            $this->sendPushNotification($receiver_id, [
                'type' => 'message',
                'title' => '💬 Nouveau message',
                'body' => $senderInfo['first_name'] . ' vous a envoyé un message',
                'icon' => 'https://cdn-icons-png.flaticon.com/512/3193/3193015.png',
                'badge' => 'https://cdn-icons-png.flaticon.com/512/732/732200.png',
                'data' => [
                    'sender_id' => $sender_id,
                    'sender_name' => $senderInfo['first_name'],
                    'message_preview' => substr($content, 0, 50) . '...',
                    'url' => '/loove/messages.php?user=' . $sender_id
                ]
            ]);
        }
        
        return $result;
    }

    // Méthode pour envoyer les notifications push
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
                                    'title' => '👀 Voir',
                                    'icon' => 'https://cdn-icons-png.flaticon.com/512/159/159604.png'
                                ],
                                [
                                    'action' => 'dismiss',
                                    'title' => '❌ Ignorer',
                                    'icon' => 'https://cdn-icons-png.flaticon.com/512/458/458594.png'
                                ]
                            ]
                        ]
                    ]
                ]
            );
            
            error_log('Notification push message envoyée à user-' . $user_id);
            return true;
            
        } catch (Exception $e) {
            error_log('Erreur notification push message: ' . $e->getMessage());
            return false;
        }
    }

    // Configuration Pusher (à personnaliser)
    private function getPusherConfig() {
        return [
            'instance_id' => 'a4e6f07d-67c8-4327-9be8-example', // Remplace par ton vrai instance ID
            'secret_key' => 'C8A1234567890ABCDEF1234567890ABCDEF1234567890' // Remplace par ta vraie clé secrète
        ];
    }

    // Icônes pour les notifications
    private function getNotificationIcon($type) {
        $icons = [
            'message' => 'https://cdn-icons-png.flaticon.com/512/3193/3193015.png',
            'like' => 'https://cdn-icons-png.flaticon.com/512/2589/2589175.png',
            'match' => 'https://cdn-icons-png.flaticon.com/512/833/833472.png',
            'visit' => 'https://cdn-icons-png.flaticon.com/512/159/159604.png'
        ];
        
        return $icons[$type] ?? 'https://cdn-icons-png.flaticon.com/512/2190/2190552.png';
    }

    // Badge pour les notifications
    private function getNotificationBadge() {
        return 'https://cdn-icons-png.flaticon.com/512/732/732200.png';
    }

    // Icônes pour les actions
    private function getActionIcon($action) {
        $icons = [
            'view' => 'https://cdn-icons-png.flaticon.com/512/159/159604.png',
            'dismiss' => 'https://cdn-icons-png.flaticon.com/512/458/458594.png',
            'reply' => 'https://cdn-icons-png.flaticon.com/512/1380/1380338.png'
        ];
        
        return $icons[$action] ?? '';
    }

    // Récupérer les infos d'un utilisateur
    private function getUserInfo($user_id) {
        $sql = "SELECT first_name, last_name, profile_picture FROM users WHERE id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Example: Get messages between two users (a chat history)
    public function getChatMessages($user1_id, $user2_id, $limit = 50, $offset = 0) {
        $sql = "SELECT m.*, u_sender.first_name as sender_name 
                FROM {$this->table} m
                JOIN users u_sender ON m.sender_id = u_sender.id
                WHERE (m.sender_id = :user1_id AND m.receiver_id = :user2_id) 
                   OR (m.sender_id = :user2_id AND m.receiver_id = :user1_id)
                ORDER BY m.sent_at ASC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user1_id', $user1_id, PDO::PARAM_INT);
        $stmt->bindParam(':user2_id', $user2_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Envoyer un message via AJAX
    public function sendMessageAjax($sender_id, $receiver_id, $content) {
        try {
            $result = $this->sendMessage($sender_id, $receiver_id, $content);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Message envoyé avec succès!',
                    'data' => [
                        'sender_id' => $sender_id,
                        'receiver_id' => $receiver_id,
                        'content' => $content,
                        'sent_at' => date('Y-m-d H:i:s')
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi du message'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }

    // Récupérer les messages via AJAX
    public function getChatMessagesAjax($user1_id, $user2_id, $limit = 50, $offset = 0) {
        try {
            $messages = $this->getChatMessages($user1_id, $user2_id, $limit, $offset);
            
            return [
                'success' => true,
                'message' => 'Messages récupérés avec succès',
                'data' => $messages,
                'count' => count($messages)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des messages: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    // Marquer un message comme lu (AJAX)
    public function markAsReadAjax($message_id, $user_id) {
        try {
            $sql = "UPDATE {$this->table} SET is_read = 1, read_at = NOW() 
                    WHERE id = :message_id AND receiver_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':message_id', $message_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $result = $stmt->execute();
            
            return [
                'success' => $result,
                'message' => $result ? 'Message marqué comme lu' : 'Erreur lors du marquage'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }

    // Supprimer un message (AJAX)
    public function deleteMessageAjax($message_id, $user_id) {
        try {
            $sql = "DELETE FROM {$this->table} 
                    WHERE id = :message_id AND sender_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':message_id', $message_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $result = $stmt->execute();
            
            return [
                'success' => $result,
                'message' => $result ? 'Message supprimé avec succès' : 'Impossible de supprimer le message'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }

    // Méthode utilitaire pour retourner du JSON
    public static function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Vérifier si la requête est AJAX
    public static function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}
