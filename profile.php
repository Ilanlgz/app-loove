<?php
session_start();

// Vérification
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config/database.php';

$conn = getDbConnection();
$message = '';
$error = '';

// Ajouter la colonne photos si elle n'existe pas
try {
    $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS photos TEXT");
} catch (PDOException $e) {
    // Ignorer si la colonne existe déjà
}

// Récupérer les informations de l'utilisateur
$query = "SELECT * FROM users WHERE id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les photos de l'utilisateur
$user_photos = isset($user['photos']) && $user['photos'] ? explode(',', $user['photos']) : [];

// Traitement du formulaire de mise à jour
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_profile'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $date_of_birth = $_POST['date_of_birth'];
        $gender = $_POST['gender'];
        $location = trim($_POST['location']);
        $occupation = trim($_POST['occupation']);
        $bio = trim($_POST['bio']);
        $interests = trim($_POST['interests']);
        $height = $_POST['height'];
        $relationship_status = $_POST['relationship_status'];
        $phone = trim($_POST['phone']);
        
        // Calculer l'âge
        $age = null;
        if ($date_of_birth) {
            $birthDate = new DateTime($date_of_birth);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;
        }
        
        // Gestion de l'upload de photo
        $profile_picture = $user['profile_picture'];
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
            $upload_dir = 'uploads/profiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $new_filename = 'profile_' . $_SESSION["user_id"] . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                    // Supprimer l'ancienne photo si elle existe
                    if ($profile_picture && file_exists($upload_dir . $profile_picture)) {
                        unlink($upload_dir . $profile_picture);
                    }
                    $profile_picture = $new_filename;
                } else {
                    $error = "Erreur lors de l'upload de la photo.";
                }
            } else {
                $error = "Format de fichier non autorisé. Utilisez JPG, JPEG, PNG ou GIF.";
            }
        }
        
        if (empty($error)) {
            // Mettre à jour la base de données
            $update_query = "UPDATE users SET 
                            first_name = :first_name,
                            last_name = :last_name,
                            email = :email,
                            date_of_birth = :date_of_birth,
                            age = :age,
                            gender = :gender,
                            location = :location,
                            occupation = :occupation,
                            bio = :bio,
                            interests = :interests,
                            height = :height,
                            relationship_status = :relationship_status,
                            phone = :phone,
                            profile_picture = :profile_picture,
                            last_active = NOW()
                            WHERE id = :user_id";
            
            $stmt = $conn->prepare($update_query);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':date_of_birth', $date_of_birth);
            $stmt->bindParam(':age', $age, PDO::PARAM_INT);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':occupation', $occupation);
            $stmt->bindParam(':bio', $bio);
            $stmt->bindParam(':interests', $interests);
            $stmt->bindParam(':height', $height, PDO::PARAM_INT);
            $stmt->bindParam(':relationship_status', $relationship_status);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':profile_picture', $profile_picture);
            $stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $message = "Profil mis à jour avec succès !";
                // Recharger les données utilisateur
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = "Erreur lors de la mise à jour du profil.";
            }
        }
    }
    
    // Traitement de l'upload de photos multiples
    if (isset($_POST['upload_photos'])) {
        $upload_dir = 'uploads/profiles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $uploaded_files = [];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (isset($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
            for ($i = 0; $i < count($_FILES['photos']['name']); $i++) {
                if ($_FILES['photos']['error'][$i] === 0) {
                    $file_extension = strtolower(pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION));
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        $new_filename = 'photo_' . $_SESSION["user_id"] . '_' . time() . '_' . $i . '.' . $file_extension;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['photos']['tmp_name'][$i], $upload_path)) {
                            $uploaded_files[] = $new_filename;
                        }
                    }
                }
            }
        }
        
        if (!empty($uploaded_files)) {
            // Récupérer les photos existantes
            $existing_photos = $user['photos'] ? explode(',', $user['photos']) : [];
            $all_photos = array_merge($existing_photos, $uploaded_files);
            $photos_string = implode(',', $all_photos);
            
            // Mettre à jour la base de données
            $update_photos = "UPDATE users SET photos = :photos WHERE id = :user_id";
            $stmt = $conn->prepare($update_photos);
            $stmt->bindParam(':photos', $photos_string);
            $stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $message = count($uploaded_files) . " photo(s) ajoutée(s) avec succès !";
                // Recharger les données utilisateur
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
    }
    
    if (isset($_POST['delete_photo'])) {
        $photo_to_delete = $_POST['photo_name'];
        $existing_photos = $user['photos'] ? explode(',', $user['photos']) : [];
        
        // Supprimer la photo du tableau
        $updated_photos = array_filter($existing_photos, function($photo) use ($photo_to_delete) {
            return $photo !== $photo_to_delete;
        });
        
        $photos_string = implode(',', $updated_photos);
        
        // Mettre à jour la base de données
        $update_photos = "UPDATE users SET photos = :photos WHERE id = :user_id";
        $stmt = $conn->prepare($update_photos);
        $stmt->bindParam(':photos', $photos_string);
        $stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            // Supprimer le fichier physique
            $file_path = 'uploads/profiles/' . $photo_to_delete;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            $message = "Photo supprimée avec succès !";
            // Recharger les données utilisateur
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    if (isset($_POST['set_profile_picture'])) {
        $new_profile_picture = $_POST['photo_name'];
        
        $update_profile_pic = "UPDATE users SET profile_picture = :profile_picture WHERE id = :user_id";
        $stmt = $conn->prepare($update_profile_pic);
        $stmt->bindParam(':profile_picture', $new_profile_picture);
        $stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $message = "Photo de profil mise à jour !";
            // Recharger les données utilisateur
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}

// Si les données utilisateur ont été mises à jour, recalculer les photos
if (isset($message)) {
    $user_photos = isset($user['photos']) && $user['photos'] ? explode(',', $user['photos']) : [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Loove</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/hearts-background.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/footer.css">
    <style>/* Reset et base avec background rouge travaillé */
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #FF4458 0%, #FF6B81 25%, #FD5068 50%, #FF8A95 75%, #FFB3C1 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(255, 68, 88, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 107, 129, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(253, 80, 104, 0.2) 0%, transparent 50%);
            z-index: -1;
            animation: floatingColors 20s ease-in-out infinite;
        }

        @keyframes floatingColors {
            0%, 100% { 
                background: 
                    radial-gradient(circle at 20% 80%, rgba(255, 68, 88, 0.3) 0%, transparent 50%),
                    radial-gradient(circle at 80% 20%, rgba(255, 107, 129, 0.3) 0%, transparent 50%),
                    radial-gradient(circle at 40% 40%, rgba(253, 80, 104, 0.2) 0%, transparent 50%);
            }
            50% { 
                background: 
                    radial-gradient(circle at 70% 30%, rgba(255, 68, 88, 0.3) 0%, transparent 50%),
                    radial-gradient(circle at 30% 70%, rgba(255, 107, 129, 0.3) 0%, transparent 50%),
                    radial-gradient(circle at 60% 60%, rgba(253, 80, 104, 0.2) 0%, transparent 50%);
            }
        }

        /* Cœurs flottants réactifs */
        .floating-hearts {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
            overflow: hidden;
        }

        .floating-heart {
            position: absolute;
            color: rgba(255, 255, 255, 0.6);
            font-size: 20px;
            animation: floatUp 8s infinite linear;
            opacity: 0;
        }

        .floating-heart:nth-child(odd) {
            color: rgba(255, 68, 88, 0.7);
            animation-duration: 10s;
        }

        .floating-heart:nth-child(3n) {
            color: rgba(255, 107, 129, 0.6);
            animation-duration: 12s;
            font-size: 16px;
        }

        .floating-heart:nth-child(4n) {
            color: rgba(253, 80, 104, 0.5);
            animation-duration: 9s;
            font-size: 24px;
        }

        .floating-heart:nth-child(5n) {
            color: rgba(255, 138, 149, 0.7);
            animation-duration: 11s;
            font-size: 18px;
        }

        @keyframes floatUp {
            0% {
                opacity: 0;
                transform: translateY(100vh) rotate(0deg) scale(0);
            }
            10% {
                opacity: 1;
                transform: translateY(90vh) rotate(45deg) scale(1);
            }
            90% {
                opacity: 1;
                transform: translateY(10vh) rotate(315deg) scale(1);
            }
            100% {
                opacity: 0;
                transform: translateY(-10vh) rotate(360deg) scale(0);
            }
        }        /* Header unifié avec taille cohérente - FORCE OVERRIDE */
        .header {
            background: linear-gradient(135deg, #FF4458, #FF6B81) !important;
            backdrop-filter: blur(20px) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
            padding: 12px 0 !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 1000 !important;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .nav-container {
            max-width: 1400px !important;
            margin: 0 auto !important;
            padding: 0 24px !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
        }

        .logo {
            font-size: 22px !important;
            font-weight: 700 !important;
            color: white !important;
            letter-spacing: 0.5px !important;
            display: flex !important;
            align-items: center !important;
            text-decoration: none !important;
            transition: opacity 0.3s ease !important;
        }

        .logo:hover {
            opacity: 0.8 !important;
        }

        .logo i {
            margin-right: 6px !important;
            font-size: 20px !important;
        }

        .nav-menu {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
        }

        .nav-link {
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
            padding: 8px 16px !important;
            color: rgba(255, 255, 255, 0.9) !important;
            text-decoration: none !important;
            border-radius: 8px !important;
            font-weight: 500 !important;
            transition: all 0.3s ease !important;
            position: relative !important;
            font-size: 14px !important;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.15) !important;
            color: white !important;
            transform: translateY(-2px) !important;
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.25) !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.2) !important;
        }

        .nav-link i {
            font-size: 16px !important;
            width: 16px !important;
            text-align: center !important;
        }

        .user-info {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            margin-left: 20px !important;
            padding-left: 20px !important;
            border-left: 1px solid rgba(255, 255, 255, 0.3) !important;
        }

        .user-avatar {
            width: 32px !important;
            height: 32px !important;
            background: white !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #FF4458 !important;
            font-weight: 600 !important;
            font-size: 14px !important;
        }

        .user-info span {
            font-weight: 500 !important;
            color: white !important;
        }

        .btn-logout {
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
            padding: 6px 12px !important;
            background: rgba(255, 255, 255, 0.15) !important;
            color: white !important;
            text-decoration: none !important;
            border-radius: 6px !important;
            font-weight: 500 !important;
            transition: all 0.3s ease !important;
            font-size: 13px !important;
        }

        .btn-logout:hover {
            background: white !important;
            color: #FF4458 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.3) !important;
        }

        /* Responsive navbar */
        @media (max-width: 768px) {
            .nav-container {
                padding: 0 16px !important;
            }
            
            .user-info span {
                display: none !important;
            }
            
            .nav-link {
                padding: 6px 12px !important;
                font-size: 13px !important;
            }
        }/* Améliorer la transparence des cartes sur le nouveau fond */
        .profile-header {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }

        .profile-form {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }

        .form-section:last-child {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 20px;
            padding: 40px;
            margin-top: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        :root {
            --primary-color: #FF4458;
            --secondary-color: #FD5068;
            --text-primary: #2c2c2c;
            --text-secondary: #8E8E93;
            --background: #FAFAFA;
            --white: #FFFFFF;
            --success: #34C759;
            --error: #FF3B30;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-primary);
        }

        .profile-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .profile-header {
            background: var(--white);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            text-align: center;
        }

        .profile-avatar {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }

        .avatar-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--white);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        .avatar-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 3rem;
            font-weight: 600;
            border: 5px solid var(--white);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        .premium-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: var(--white);
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .profile-name {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .profile-info {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 20px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .profile-form {
            background: var(--white);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }

        .form-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .form-input, .form-select, .form-textarea {
            padding: 15px 20px;
            border: 2px solid #E8E8E8;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
            background: var(--white);
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 68, 88, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 20px;
            border: 2px dashed var(--primary-color);
            border-radius: 12px;
            color: var(--primary-color);
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            background: rgba(255, 68, 88, 0.05);
        }

        .interests-input {
            position: relative;
        }

        .interests-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .interest-tag {
            background: rgba(255, 68, 88, 0.1);
            color: var(--primary-color);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .interest-tag:hover {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-save {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 15px 40px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(255, 68, 88, 0.3);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(52, 199, 89, 0.1);
            color: var(--success);
            border: 1px solid rgba(52, 199, 89, 0.3);
        }

        .alert-error {
            background: rgba(255, 59, 48, 0.1);
            color: #000000;
            border: 1px solid rgba(255, 59, 48, 0.2);
            font-weight: 600;
        }

        .photos-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .photo-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .photo-item:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }

        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .photo-item:hover .photo-overlay {
            opacity: 1;
        }

        .btn-photo-action {
            background: var(--white);
            color: var(--text-primary);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-photo-action:hover {
            transform: scale(1.1);
        }

        .btn-photo-action.delete {
            background: var(--error);
            color: var(--white);
        }

        .profile-badge {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, var(--premium-color), var(--premium-secondary));
            color: var(--white);
            padding: 5px;
            text-align: center;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .photo-upload-slot {
            aspect-ratio: 1;
            border: 2px dashed var(--text-secondary);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--text-secondary);
        }

        .photo-upload-slot:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: rgba(255, 68, 88, 0.05);
        }

        .photo-upload-slot i {
            font-size: 2rem;
            margin-bottom: 8px;
        }

        .photo-upload-slot span {
            font-size: 0.9rem;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .profile-stats {
                gap: 20px;
            }
            
            .profile-container {
                padding: 20px 10px;
            }
            
            .profile-header, .profile-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Cœurs flottants réactifs -->
    <div class="floating-hearts" id="floatingHearts"></div>

    <!-- Container des coeurs flottants -->
    <div class="hearts-container">
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
    </div>

    <?php include 'includes/navbar.php'; ?>
    
    <div class="profile-container">
        <!-- En-tête du profil -->
        <div class="profile-header">
            <div class="profile-avatar">
                <?php if ($user['profile_picture']): ?>
                    <img src="uploads/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Photo de profil" class="avatar-img">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($user['is_premium']): ?>
                    <div class="premium-badge">
                        <i class="fas fa-crown"></i> Premium
                    </div>
                <?php endif; ?>
            </div>
            
            <h1 class="profile-name">
                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
            </h1>
            
            <div class="profile-info">
                <?php if ($user['age']): ?>
                    <?php echo $user['age']; ?> ans
                <?php endif; ?>
                <?php if ($user['location']): ?>
                    • <?php echo htmlspecialchars($user['location']); ?>
                <?php endif; ?>
                <?php if ($user['occupation']): ?>
                    • <?php echo htmlspecialchars($user['occupation']); ?>
                <?php endif; ?>
            </div>
            
            <div class="profile-stats">
                <div class="stat-item">
                    <div class="stat-number">
                        <?php
                        // Compter les matches
                        $matches_query = "SELECT COUNT(*) as count FROM matches WHERE user1_id = :user_id1 OR user2_id = :user_id2";
                        $stmt = $conn->prepare($matches_query);
                        $stmt->bindParam(':user_id1', $_SESSION["user_id"], PDO::PARAM_INT);
                        $stmt->bindParam(':user_id2', $_SESSION["user_id"], PDO::PARAM_INT);
                        $stmt->execute();
                        $matches_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                        echo $matches_count;
                        ?>
                    </div>
                    <div class="stat-label">Matches</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">
                        <?php
                        // Compter les likes donnés
                        $likes_query = "SELECT COUNT(*) as count FROM likes WHERE user_id = :user_id";
                        $stmt = $conn->prepare($likes_query);
                        $stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
                        $stmt->execute();
                        $likes_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                        echo $likes_count;
                        ?>
                    </div>
                    <div class="stat-label">Likes donnés</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">
                        <?php
                        // Compter les jours depuis l'inscription
                        $join_date = new DateTime($user['created_at']);
                        $today = new DateTime();
                        $days = $today->diff($join_date)->days;
                        echo $days;
                        ?>
                    </div>
                    <div class="stat-label">Jours sur Loove</div>
                </div>
            </div>
        </div>

        <!-- Formulaire de modification -->
        <div class="profile-form">
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <!-- Informations personnelles -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-user"></i>
                        Informations personnelles
                    </h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Prénom *</label>
                            <input type="text" name="first_name" class="form-input" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nom *</label>
                            <input type="text" name="last_name" class="form-input" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Date de naissance</label>
                            <input type="date" name="date_of_birth" class="form-input" value="<?php echo $user['date_of_birth']; ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Genre</label>
                            <select name="gender" class="form-select">
                                <option value="">Sélectionner</option>
                                <option value="male" <?php echo $user['gender'] === 'male' ? 'selected' : ''; ?>>Homme</option>
                                <option value="female" <?php echo $user['gender'] === 'female' ? 'selected' : ''; ?>>Femme</option>
                                <option value="other" <?php echo $user['gender'] === 'other' ? 'selected' : ''; ?>>Autre</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Localisation et travail -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-map-marker-alt"></i>
                        Localisation & Profession
                    </h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Ville</label>
                            <input type="text" name="location" class="form-input" value="<?php echo htmlspecialchars($user['location']); ?>" placeholder="Ex: Paris, France">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Profession</label>
                            <input type="text" name="occupation" class="form-input" value="<?php echo htmlspecialchars($user['occupation']); ?>" placeholder="Ex: Développeur">
                        </div>
                    </div>
                </div>

                <!-- Informations physiques -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-ruler-vertical"></i>
                        Informations physiques
                    </h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Taille (cm)</label>
                            <input type="number" name="height" class="form-input" value="<?php echo $user['height']; ?>" min="120" max="250">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Statut relationnel</label>
                            <select name="relationship_status" class="form-select">
                                <option value="">Sélectionner</option>
                                <option value="single" <?php echo $user['relationship_status'] === 'single' ? 'selected' : ''; ?>>Célibataire</option>
                                <option value="divorced" <?php echo $user['relationship_status'] === 'divorced' ? 'selected' : ''; ?>>Divorcé(e)</option>
                                <option value="separated" <?php echo $user['relationship_status'] === 'separated' ? 'selected' : ''; ?>>Séparé(e)</option>
                                <option value="widowed" <?php echo $user['relationship_status'] === 'widowed' ? 'selected' : ''; ?>>Veuf/Veuve</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Photo de profil -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-camera"></i>
                        Photo de profil
                    </h2>
                    
                    <div class="form-group">
                        <label class="form-label">Changer la photo</label>
                        <div class="file-input-wrapper">
                            <input type="file" name="profile_picture" class="file-input" accept="image/*">
                            <div class="file-input-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                Choisir une photo (JPG, PNG, GIF)
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Biographie -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-quote-left"></i>
                        À propos de moi
                    </h2>
                    
                    <div class="form-group full-width">
                        <label class="form-label">Biographie</label>
                        <textarea name="bio" class="form-textarea" placeholder="Parlez-nous de vous, vos passions, ce que vous recherchez..."><?php echo htmlspecialchars($user['bio']); ?></textarea>
                    </div>
                </div>

                <!-- Centres d'intérêt -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-heart"></i>
                        Centres d'intérêt
                    </h2>
                    
                    <div class="form-group full-width">
                        <label class="form-label">Vos centres d'intérêt (séparés par des virgules)</label>
                        <div class="interests-input">
                            <input type="text" name="interests" class="form-input" value="<?php echo htmlspecialchars($user['interests']); ?>" placeholder="Ex: Voyage, Cuisine, Sport, Cinéma">
                            <div class="interests-suggestions">
                                <span class="interest-tag" onclick="addInterest('Voyage')">Voyage</span>
                                <span class="interest-tag" onclick="addInterest('Cuisine')">Cuisine</span>
                                <span class="interest-tag" onclick="addInterest('Sport')">Sport</span>
                                <span class="interest-tag" onclick="addInterest('Cinéma')">Cinéma</span>
                                <span class="interest-tag" onclick="addInterest('Lecture')">Lecture</span>
                                <span class="interest-tag" onclick="addInterest('Musique')">Musique</span>
                                <span class="interest-tag" onclick="addInterest('Art')">Art</span>
                                <span class="interest-tag" onclick="addInterest('Nature')">Nature</span>
                                <span class="interest-tag" onclick="addInterest('Technologie')">Technologie</span>
                                <span class="interest-tag" onclick="addInterest('Danse')">Danse</span>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" name="update_profile" class="btn-save">
                    <i class="fas fa-save"></i>
                    Sauvegarder les modifications
                </button>
            </form>
        </div>

        <!-- Galerie de photos -->
        <div class="form-section">
            <h2 class="section-title">
                <i class="fas fa-images"></i>
                Mes Photos (<?php echo count($user_photos); ?>/6)
            </h2>
            
            <div class="photos-gallery">
                <?php if (!empty($user_photos)): ?>
                    <?php foreach ($user_photos as $index => $photo): ?>
                        <div class="photo-item">
                            <img src="uploads/profiles/<?php echo htmlspecialchars($photo); ?>" alt="Photo <?php echo $index + 1; ?>">
                            <div class="photo-overlay">
                                <button type="button" class="btn-photo-action" onclick="setAsProfilePicture('<?php echo $photo; ?>')" title="Définir comme photo de profil">
                                    <i class="fas fa-star"></i>
                                </button>
                                <button type="button" class="btn-photo-action delete" onclick="deletePhoto('<?php echo $photo; ?>')" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <?php if (isset($user['profile_picture']) && $user['profile_picture'] === $photo): ?>
                                <div class="profile-badge">
                                    <i class="fas fa-crown"></i> Photo de profil
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if (count($user_photos) < 6): ?>
                    <div class="photo-upload-slot" onclick="document.getElementById('photosInput').click()">
                        <i class="fas fa-plus"></i>
                        <span>Ajouter des photos</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <form method="POST" enctype="multipart/form-data" style="display: none;">
                <input type="file" id="photosInput" name="photos[]" accept="image/*" multiple onchange="uploadPhotos(this)">
                <input type="hidden" name="upload_photos" value="1">
            </form>
        </div>
    </div>
    
    <script>
        function addInterest(interest) {
            const input = document.querySelector('input[name="interests"]');
            let currentInterests = input.value.trim();
            
            if (currentInterests === '') {
                input.value = interest;
            } else {
                const interestsArray = currentInterests.split(',').map(i => i.trim());
                if (!interestsArray.includes(interest)) {
                    input.value = currentInterests + ', ' + interest;
                }
            }
        }

        function uploadPhotos(input) {
            if (input.files && input.files.length > 0) {
                input.closest('form').submit();
            }
        }

        function setAsProfilePicture(photoName) {
            if (confirm('Définir cette photo comme photo de profil ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="set_profile_picture" value="1">
                    <input type="hidden" name="photo_name" value="${photoName}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deletePhoto(photoName) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette photo ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="delete_photo" value="1">
                    <input type="hidden" name="photo_name" value="${photoName}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Animation des alertes
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.parentNode.removeChild(alert);
                        }
                    }, 300);
                }, 5000);
            });
        });

        // Preview de l'image uploadée
        document.querySelector('.file-input').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const avatarImg = document.querySelector('.avatar-img');
                    const avatarPlaceholder = document.querySelector('.avatar-placeholder');
                    if (avatarImg) {
                        avatarImg.src = e.target.result;
                    } else if (avatarPlaceholder) {
                        avatarPlaceholder.innerHTML = `<img src="${e.target.result}" alt="Avatar" class="avatar-img">`;
                    }
                };                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // Génération dynamique des cœurs flottants réactifs
        function createFloatingHearts() {
            const heartsContainer = document.getElementById('floatingHearts');
            if (!heartsContainer) return;

            // Créer des cœurs avec des positions et timings aléatoires
            for (let i = 0; i < 15; i++) {
                setTimeout(() => {
                    createHeart();
                }, i * 800);
            }

            // Créer un nouveau cœur toutes les 3 secondes
            setInterval(createHeart, 3000);
        }

        function createHeart() {
            const heartsContainer = document.getElementById('floatingHearts');
            if (!heartsContainer) return;

            const heart = document.createElement('i');
            heart.className = 'fas fa-heart floating-heart';
            
            // Position horizontale aléatoire
            heart.style.left = Math.random() * 100 + '%';
            
            // Délai d'animation aléatoire
            heart.style.animationDelay = Math.random() * 2 + 's';
            
            // Taille légèrement variable
            const size = 16 + Math.random() * 12;
            heart.style.fontSize = size + 'px';

            // Couleurs aléatoires romantiques
            const colors = [
                'rgba(255, 68, 88, 0.7)',
                'rgba(255, 107, 129, 0.6)', 
                'rgba(253, 80, 104, 0.5)',
                'rgba(255, 138, 149, 0.7)',
                'rgba(255, 255, 255, 0.6)'
            ];
            heart.style.color = colors[Math.floor(Math.random() * colors.length)];

            heartsContainer.appendChild(heart);

            // Supprimer le cœur après l'animation
            setTimeout(() => {
                if (heart.parentNode) {
                    heart.parentNode.removeChild(heart);
                }
            }, 12000);
        }

        // Explosion de cœurs lors de la sauvegarde
        document.querySelector('.btn-save').addEventListener('click', function() {
            for (let i = 0; i < 8; i++) {
                setTimeout(() => {
                    createHeart();
                }, i * 150);
            }
        });

        // Explosion de cœurs lors de l'upload de photos
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-photo-action') || e.target.closest('.photo-upload-slot')) {
                for (let i = 0; i < 5; i++) {
                    setTimeout(() => {
                        createHeart();
                    }, i * 100);
                }
            }
        });

        // Démarrer les cœurs au chargement
        document.addEventListener('DOMContentLoaded', function() {
            createFloatingHearts();        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
