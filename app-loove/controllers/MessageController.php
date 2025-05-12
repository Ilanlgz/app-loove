<?php
class MessageController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->addCss('/css/messages.css');
        $this->addCss('/css/utilities.css');
    }
    
    public function handleRequest() {
        // Vérifier si l'utilisateur est connecté
        $this->requireLogin();
        
        $request_uri = $_SERVER['REQUEST_URI'];
        
        if (strpos($request_uri, '/message/view/') !== false) {
            // Extraire l'ID de conversation de l'URL
            $parts = explode('/message/view/', $request_uri);
            $conversation_id = intval(end($parts));
            $this->viewConversation($conversation_id);
        } elseif (strpos($request_uri, '/message/new/') !== false) {
            // Extraire l'ID de l'utilisateur de l'URL
            $parts = explode('/message/new/', $request_uri);
            $recipient_id = intval(end($parts));
            $this->newMessage($recipient_id);
        } else {
            // Liste de toutes les conversations
            $this->listConversations();
        }
    }
    
    private function listConversations() {
        $this->setTitle('Messages - Loove Dating App');
        
        $user_id = $_SESSION['user_id'];
        $conversations = [];
        
        // Obtenir toutes les conversations de l'utilisateur
        $db = getDatabaseConnection();
        
        $sql = "SELECT DISTINCT 
                    c.id as conversation_id,
                    u.id as user_id,
                    u.name as user_name,
                    u.profile_picture,
                    (SELECT content FROM messages 
                        WHERE conversation_id = c.id 
                        ORDER BY created_at DESC LIMIT 1) as last_message,
                    (SELECT created_at FROM messages 
                        WHERE conversation_id = c.id 
                        ORDER BY created_at DESC LIMIT 1) as last_message_time
                FROM conversations c
                JOIN conversation_participants cp ON c.id = cp.conversation_id
                JOIN users u ON (cp.user_id = u.id AND u.id != ?)
                WHERE c.id IN (
                    SELECT conversation_id FROM conversation_participants 
                    WHERE user_id = ?
                )
                ORDER BY last_message_time DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $conversations[] = $row;
            }
        }
        
        $db->close();
        
        $this->render('messages/list', [
            'conversations' => $conversations
        ]);
    }
    
    private function viewConversation($conversation_id) {
        $this->setTitle('Conversation - Loove Dating App');
        
        $user_id = $_SESSION['user_id'];
        $messages = [];
        $other_user = null;
        
        if ($conversation_id) {
            $db = getDatabaseConnection();
            
            // Vérifier que l'utilisateur fait partie de cette conversation
            $check_sql = "SELECT COUNT(*) as count FROM conversation_participants 
                          WHERE conversation_id = ? AND user_id = ?";
            $check_stmt = $db->prepare($check_sql);
            $check_stmt->bind_param("ii", $conversation_id, $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $check_row = $check_result->fetch_assoc();
            
            if ($check_row['count'] > 0) {
                // Obtenir l'autre utilisateur dans la conversation
                $user_sql = "SELECT u.id, u.name, u.profile_picture 
                             FROM users u
                             JOIN conversation_participants cp ON u.id = cp.user_id
                             WHERE cp.conversation_id = ? AND u.id != ?";
                $user_stmt = $db->prepare($user_sql);
                $user_stmt->bind_param("ii", $conversation_id, $user_id);
                $user_stmt->execute();
                $user_result = $user_stmt->get_result();
                $other_user = $user_result->fetch_assoc();
                
                // Obtenir tous les messages de la conversation
                $msg_sql = "SELECT m.id, m.sender_id, m.content, m.created_at, u.name as sender_name
                            FROM messages m
                            JOIN users u ON m.sender_id = u.id
                            WHERE m.conversation_id = ?
                            ORDER BY m.created_at ASC";
                $msg_stmt = $db->prepare($msg_sql);
                $msg_stmt->bind_param("i", $conversation_id);
                $msg_stmt->execute();
                $msg_result = $msg_stmt->get_result();
                
                while ($row = $msg_result->fetch_assoc()) {
                    $messages[] = $row;
                }
                
                // Marquer les messages comme lus
                $update_sql = "UPDATE messages 
                               SET is_read = 1 
                               WHERE conversation_id = ? AND sender_id != ? AND is_read = 0";
                $update_stmt = $db->prepare($update_sql);
                $update_stmt->bind_param("ii", $conversation_id, $user_id);
                $update_stmt->execute();
            }
            
            $db->close();
        }
        
        $this->render('messages/view', [
            'conversation_id' => $conversation_id,
            'other_user' => $other_user,
            'messages' => $messages
        ]);
    }
    
    private function newMessage($recipient_id) {
        $this->setTitle('New Message - Loove Dating App');
        
        $user_id = $_SESSION['user_id'];
        $recipient = null;
        $existing_conversation_id = null;
        $error = null;
        $success = false;
        
        if ($recipient_id) {
            $db = getDatabaseConnection();
            
            // Vérifier que le destinataire existe
            $user_sql = "SELECT id, name, profile_picture FROM users WHERE id = ?";
            $user_stmt = $db->prepare($user_sql);
            $user_stmt->bind_param("i", $recipient_id);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            
            if ($user_result->num_rows > 0) {
                $recipient = $user_result->fetch_assoc();
                
                // Vérifier s'il existe déjà une conversation entre ces utilisateurs
                $conv_sql = "SELECT c.id FROM conversations c
                             JOIN conversation_participants cp1 ON c.id = cp1.conversation_id
                             JOIN conversation_participants cp2 ON c.id = cp2.conversation_id
                             WHERE cp1.user_id = ? AND cp2.user_id = ?";
                $conv_stmt = $db->prepare($conv_sql);
                $conv_stmt->bind_param("ii", $user_id, $recipient_id);
                $conv_stmt->execute();
                $conv_result = $conv_stmt->get_result();
                
                if ($conv_result->num_rows > 0) {
                    $conv_row = $conv_result->fetch_assoc();
                    $existing_conversation_id = $conv_row['id'];
                }
                
                // Traiter l'envoi de message si le formulaire est soumis
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
                    $content = trim($_POST['content']);
                    
                    if (!empty($content)) {
                        $conversation_id = $existing_conversation_id;
                        
                        // Créer une nouvelle conversation si nécessaire
                        if (!$conversation_id) {
                            $create_conv_sql = "INSERT INTO conversations (created_at) VALUES (NOW())";
                            $db->query($create_conv_sql);
                            $conversation_id = $db->insert_id;
                            
                            // Ajouter les participants
                            $part_sql = "INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)";
                            $part_stmt = $db->prepare($part_sql);
                            $part_stmt->bind_param("ii", $conversation_id, $user_id);
                            $part_stmt->execute();
                            
                            $part_stmt->bind_param("ii", $conversation_id, $recipient_id);
                            $part_stmt->execute();
                        }
                        
                        // Enregistrer le message
                        $msg_sql = "INSERT INTO messages (conversation_id, sender_id, content, created_at) 
                                    VALUES (?, ?, ?, NOW())";
                        $msg_stmt = $db->prepare($msg_sql);
                        $msg_stmt->bind_param("iis", $conversation_id, $user_id, $content);
                        
                        if ($msg_stmt->execute()) {
                            $success = true;
                            // Rediriger vers la conversation
                            header("Location: /loove/app-loove/public/message/view/" . $conversation_id);
                            exit;
                        } else {
                            $error = "Échec de l'envoi du message. Veuillez réessayer.";
                        }
                    } else {
                        $error = "Le message ne peut pas être vide.";
                    }
                }
            } else {
                $error = "Destinataire introuvable.";
            }
            
            $db->close();
        } else {
            $error = "ID de destinataire non spécifié.";
        }
        
        $this->render('messages/new', [
            'recipient' => $recipient,
            'existing_conversation_id' => $existing_conversation_id,
            'error' => $error,
            'success' => $success
        ]);
    }
}
?>