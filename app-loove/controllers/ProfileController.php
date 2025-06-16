<?php
class ProfileController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->addCss('/css/profile.css');
    }
    
    public function handleRequest() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            // Rediriger vers la page de connexion si non connecté
            header('Location: /loove/app-loove/public/login');
            exit;
        }
        
        $request_uri = $_SERVER['REQUEST_URI'];
        
        if (strpos($request_uri, '/profile/edit') !== false) {
            $this->editProfile();
        } elseif (strpos($request_uri, '/profile/view/') !== false) {
            // Extraire l'ID du profil à afficher
            $parts = explode('/profile/view/', $request_uri);
            $user_id = intval(end($parts));
            $this->viewProfile($user_id);
        } else {
            // Page de profil par défaut (profil de l'utilisateur connecté)
            $this->myProfile();
        }
    }
    
    private function myProfile() {
        $this->setTitle('Mon Profil - Loove Dating App');
        
        $user_id = $_SESSION['user_id'];
        $db = getDatabaseConnection();
        
        // Récupérer les informations de l'utilisateur
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        $db->close();
        
        $this->render('profile/my_profile', [
            'user' => $user
        ]);
    }
    
    private function viewProfile($user_id) {
        $this->setTitle('Profil - Loove Dating App');
        
        $current_user_id = $_SESSION['user_id'];
        $db = getDatabaseConnection();
        
        // Récupérer les informations de l'utilisateur demandé
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // L'utilisateur n'existe pas
            $this->render('errors/404', [
                'message' => 'Utilisateur introuvable'
            ]);
            $db->close();
            return;
        }
        
        $user = $result->fetch_assoc();
        $db->close();
        
        $this->render('profile/view_profile', [
            'user' => $user,
            'is_own_profile' => ($user_id == $current_user_id)
        ]);
    }
    
    private function editProfile() {
        $this->setTitle('Modifier mon profil - Loove Dating App');
        
        $user_id = $_SESSION['user_id'];
        $db = getDatabaseConnection();
        $success = false;
        $errors = [];
        
        // Si le formulaire est soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $name = trim($_POST['name'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            $gender = $_POST['gender'] ?? '';
            $birthdate = $_POST['birthdate'] ?? '';
            
            // Valider les données
            if (empty($name)) {
                $errors[] = "Le nom est requis";
            }
            
            // Si pas d'erreur, mettre à jour le profil
            if (empty($errors)) {
                $sql = "UPDATE users SET name = ?, bio = ?, gender = ?, birthdate = ? WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("ssssi", $name, $bio, $gender, $birthdate, $user_id);
                
                if ($stmt->execute()) {
                    $success = true;
                } else {
                    $errors[] = "Erreur lors de la mise à jour du profil: " . $db->error;
                }
                
                // Traitement de l'image de profil si une nouvelle est téléchargée
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['profile_picture']['name'];
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    
                    if (in_array(strtolower($ext), $allowed)) {
                        $new_filename = uniqid() . '.' . $ext;
                        $upload_dir = __DIR__ . '/../public/uploads/profiles/';
                        
                        // Créer le répertoire s'il n'existe pas
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        $destination = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destination)) {
                            // Mettre à jour l'image dans la base de données
                            $sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
                            $stmt = $db->prepare($sql);
                            $stmt->bind_param("si", $new_filename, $user_id);
                            $stmt->execute();
                        } else {
                            $errors[] = "Erreur lors du téléchargement de l'image";
                        }
                    } else {
                        $errors[] = "Format d'image non autorisé. Utilisez JPG, JPEG, PNG ou GIF.";
                    }
                }
            }
        }
        
        // Récupérer les informations actuelles de l'utilisateur
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        $db->close();
        
        $this->render('profile/edit_profile', [
            'user' => $user,
            'success' => $success,
            'errors' => $errors
        ]);
    }
}
?>