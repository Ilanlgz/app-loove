<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once 'config/database.php';

$conn = getDbConnection();

// Récupérer les informations de l'utilisateur
$query = "SELECT * FROM users WHERE id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Créer les colonnes premium dans la table users si elles n'existent pas
try {
    // Ajouter les colonnes premium si elles n'existent pas
    $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS is_premium BOOLEAN DEFAULT FALSE");
    $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS premium_expires_at TIMESTAMP NULL");
} catch (PDOException $e) {
    // Si les colonnes existent déjà, ignorer l'erreur
}

// Créer la table des transactions si elle n'existe pas
try {
    $transactionsQuery = "CREATE TABLE IF NOT EXISTS premium_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        plan_type VARCHAR(50) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        transaction_id VARCHAR(100) UNIQUE,
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NULL,
        INDEX idx_user (user_id),
        INDEX idx_status (status)
    )";
    $conn->exec($transactionsQuery);
} catch (PDOException $e) {
    // Ignorer les erreurs
}

// Traitement du paiement
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['purchase_premium'])) {
    $plan_type = $_POST['plan_type'];
    $payment_method = $_POST['payment_method'];
    $card_number = $_POST['card_number'] ?? '';
    $card_expiry = $_POST['card_expiry'] ?? '';
    $card_cvv = $_POST['card_cvv'] ?? '';
    
    $errors = [];
    
    // Validation des informations de paiement selon la méthode
    if ($payment_method === 'card') {
        // Validation des informations de carte
        if (empty($card_number)) {
            $errors[] = "Le numéro de carte est requis.";
        } elseif (strlen(str_replace(' ', '', $card_number)) < 16) {
            $errors[] = "Le numéro de carte doit contenir au moins 16 chiffres.";
        }
        
        if (empty($card_expiry)) {
            $errors[] = "La date d'expiration est requise.";
        } elseif (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $card_expiry)) {
            $errors[] = "Format de date d'expiration invalide (MM/AA).";
        } else {
            // Vérifier que la carte n'est pas expirée
            list($month, $year) = explode('/', $card_expiry);
            $expiry_date = new DateTime('20' . $year . '-' . $month . '-01');
            $today = new DateTime();
            if ($expiry_date < $today) {
                $errors[] = "Cette carte bancaire est expirée.";
            }
        }
        
        if (empty($card_cvv)) {
            $errors[] = "Le code CVV est requis.";
        } elseif (strlen($card_cvv) < 3) {
            $errors[] = "Le code CVV doit contenir 3 chiffres.";
        }
        
        // Validation basique du numéro de carte (algorithme de Luhn simplifié)
        $card_clean = str_replace(' ', '', $card_number);
        if (!ctype_digit($card_clean)) {
            $errors[] = "Le numéro de carte ne doit contenir que des chiffres.";
        }
    } elseif (in_array($payment_method, ['paypal', 'apple', 'google'])) {
        // Pour les autres méthodes, on simule une validation
        // En réalité, on redirigerait vers leur API
        $errors[] = "Cette méthode de paiement n'est pas encore disponible. Utilisez une carte bancaire.";
    }
    
    // Définir les prix et durées
    $plans = [
        'weekly' => ['price' => 9.99, 'duration' => '1 WEEK'],
        'monthly' => ['price' => 29.99, 'duration' => '1 MONTH'],
        'yearly' => ['price' => 299.99, 'duration' => '1 YEAR']
    ];
    
    if (!isset($plans[$plan_type])) {
        $errors[] = "Plan d'abonnement invalide.";
    }
    
    if (empty($errors)) {
        $amount = $plans[$plan_type]['price'];
        $duration = $plans[$plan_type]['duration'];
        
        // Simuler le processus de paiement avec validation
        $payment_success = processPayment($card_number, $card_expiry, $card_cvv, $amount);
        
        if ($payment_success) {
            // Calculer la date d'expiration
            $expires_at = date('Y-m-d H:i:s', strtotime("+{$duration}"));
            
            // Enregistrer la transaction
            $transaction_id = 'TXN_' . time() . '_' . rand(1000, 9999);
            $insert_transaction = "INSERT INTO premium_transactions 
                                  (user_id, plan_type, amount, payment_method, transaction_id, status, expires_at) 
                                  VALUES (:user_id, :plan_type, :amount, :payment_method, :transaction_id, 'completed', :expires_at)";
            
            $stmt = $conn->prepare($insert_transaction);
            $stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
            $stmt->bindParam(':plan_type', $plan_type);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':payment_method', $payment_method);
            $stmt->bindParam(':transaction_id', $transaction_id);
            $stmt->bindParam(':expires_at', $expires_at);
            $stmt->execute();
            
            // Mettre à jour le statut premium de l'utilisateur
            $update_user = "UPDATE users SET is_premium = 1, premium_expires_at = :expires_at WHERE id = :user_id";
            $stmt = $conn->prepare($update_user);
            $stmt->bindParam(':expires_at', $expires_at);
            $stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
            $stmt->execute();
            
            $success_message = "Félicitations ! Votre abonnement Premium a été activé avec succès !";
            
            // Recharger les données utilisateur
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $errors[] = "Paiement refusé. Vérifiez vos informations bancaires.";
        }
    }
    
    if (!empty($errors)) {
        $error_message = implode('<br>', $errors);
    }
}

// Traitement de l'annulation premium
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_premium'])) {
    try {
        // Mettre à jour le statut premium de l'utilisateur
        $cancel_query = "UPDATE users SET is_premium = 0, premium_expires_at = NULL WHERE id = :user_id";
        $stmt = $conn->prepare($cancel_query);
        $stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            // Enregistrer l'annulation dans les transactions
            $cancel_transaction = "INSERT INTO premium_transactions 
                                  (user_id, plan_type, amount, payment_method, transaction_id, status, created_at) 
                                  VALUES (:user_id, 'cancellation', 0.00, 'system', :transaction_id, 'cancelled', NOW())";
            
            $transaction_id = 'CANCEL_' . time() . '_' . rand(1000, 9999);
            $stmt = $conn->prepare($cancel_transaction);
            $stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
            $stmt->bindParam(':transaction_id', $transaction_id);
            $stmt->execute();
            
            $success_message = "Votre abonnement Premium a été annulé avec succès.";
            
            // Recharger les données utilisateur
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error_message = "Erreur lors de l'annulation de l'abonnement.";
        }
    } catch (PDOException $e) {
        $error_message = "Erreur système lors de l'annulation.";
    }
}

// Fonction de simulation de processus de paiement
function processPayment($card_number, $card_expiry, $card_cvv, $amount) {
    $card_clean = str_replace(' ', '', $card_number);
    
    // Cartes de test qui fonctionnent (pour les démos)
    $valid_test_cards = [
        '4111111111111111', // Visa test
        '5555555555554444', // Mastercard test
        '4000000000000002', // Visa test 2
    ];
    
    // Si c'est une carte de test, accepter
    if (in_array($card_clean, $valid_test_cards)) {
        return true;
    }
    
    // Sinon, rejeter le paiement (en production, on ferait appel à une vraie API)
    return false;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium - Loove</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #FF4458;
            --secondary-color: #FD5068;
            --premium-color: #FFD700;
            --premium-secondary: #FFA500;
            --text-primary: #2c2c2c;
            --text-secondary: #8E8E93;
            --background: #FAFAFA;
            --white: #FFFFFF;
            --success: #34C759;
            --error: #FF3B30;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--text-primary);
            min-height: 100vh;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }

        .nav-menu {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-link {
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary-color);
            background: rgba(255, 68, 88, 0.1);
        }

        .btn-logout {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }

        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
        }

        .hero-section {
            text-align: center;
            margin-bottom: 80px;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 20px;
            text-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        .hero-subtitle {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .premium-crown {
            font-size: 5rem;
            color: var(--premium-color);
            margin-bottom: 30px;
            text-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from { text-shadow: 0 0 20px rgba(255, 215, 0, 0.5); }
            to { text-shadow: 0 0 30px rgba(255, 215, 0, 0.8), 0 0 40px rgba(255, 215, 0, 0.3); }
        }

        .plans-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .plan-card {
            background: var(--white);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 16px 32px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .plan-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .plan-card.featured {
            border: 3px solid var(--premium-color);
            transform: scale(1.05);
        }

        .plan-card.featured::before {
            content: "POPULAIRE";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, var(--premium-color), var(--premium-secondary));
            color: var(--white);
            padding: 10px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .plan-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            margin-top: 20px;
        }

        .plan-price {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .plan-period {
            color: var(--text-secondary);
            margin-bottom: 30px;
        }

        .plan-features {
            list-style: none;
            margin-bottom: 40px;
        }

        .plan-features li {
            padding: 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .plan-features i {
            color: var(--success);
            font-size: 1.2rem;
        }

        .btn-select-plan {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 1.1rem;
        }

        .btn-select-plan:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(255, 68, 88, 0.3);
        }

        .plan-card.featured .btn-select-plan {
            background: linear-gradient(135deg, var(--premium-color), var(--premium-secondary));
            color: var(--text-primary);
        }

        .current-status {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 40px;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .status-premium {
            background: linear-gradient(135deg, var(--premium-color), var(--premium-secondary));
        }

        .payment-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .payment-content {
            background: var(--white);
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .payment-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .form-input, .form-select {
            width: 100%;
            padding: 15px;
            border: 2px solid #E8E8E8;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 68, 88, 0.1);
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .payment-method {
            border: 2px solid #E8E8E8;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover, .payment-method.selected {
            border-color: var(--primary-color);
            background: rgba(255, 68, 88, 0.05);
        }

        .payment-method i {
            font-size: 1.5rem;
            margin-bottom: 8px;
            color: var(--primary-color);
        }

        .btn-pay {
            background: linear-gradient(135deg, var(--success), #28a745);
            color: var(--white);
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            font-size: 1.1rem;
        }

        .btn-cancel {
            background: var(--text-secondary);
            color: var(--white);
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-right: 10px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin: 60px 0;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--premium-color);
            margin-bottom: 20px;
        }

        .feature-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
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

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .plans-container {
                grid-template-columns: 1fr;
            }
            
            .plan-card.featured {
                transform: none;
            }
            
            .main-content {
                padding: 40px 10px;
            }
        }

        .premium-status-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .premium-actions {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .btn-cancel-premium {
            background: var(--error);
            color: var(--white);
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel-premium:hover {
            background: #d32f2f;
            transform: translateY(-2px);
        }

        .btn-manage {
            background: var(--text-secondary);
            color: var(--white);
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-manage:hover {
            background: #555;
            transform: translateY(-2px);
        }

        .cancel-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .cancel-content {
            background: var(--white);
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .cancel-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .cancel-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--error);
        }

        .cancel-info {
            margin-bottom: 30px;
        }

        .info-item {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding: 20px;
            background: rgba(255, 59, 48, 0.05);
            border-radius: 12px;
            border-left: 4px solid var(--error);
        }

        .info-item i {
            color: var(--error);
            font-size: 1.2rem;
            margin-top: 2px;
        }

        .info-item ul {
            margin-top: 10px;
            margin-left: 20px;
        }

        .info-item li {
            margin-bottom: 5px;
        }

        .cancel-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn-keep-premium {
            background: linear-gradient(135deg, var(--premium-color), var(--premium-secondary));
            color: var(--text-primary);
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-keep-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(255, 215, 0, 0.3);
        }

        .btn-confirm-cancel {
            background: var(--error);
            color: var(--white);
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-confirm-cancel:hover {
            background: #d32f2f;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .premium-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .cancel-actions {
                flex-direction: column;
            }
            
            .btn-keep-premium,
            .btn-confirm-cancel {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="main.php" class="logo">
                <i class="fas fa-heart"></i> Loove
            </a>
            <nav class="nav-menu">
                <a href="discover.php" class="nav-link">
                    <i class="fas fa-search"></i> Découvrir
                </a>
                <a href="matches.php" class="nav-link">
                    <i class="fas fa-heart"></i> Matches
                </a>
                <a href="messages.php" class="nav-link">
                    <i class="fas fa-comments"></i> Messages
                </a>
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user"></i> Profil
                </a>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Statut actuel -->
        <div class="current-status <?php echo $user['is_premium'] ? 'status-premium' : ''; ?>">
            <?php if ($user['is_premium']): ?>
                <div class="premium-status-header">
                    <h2><i class="fas fa-crown"></i> Vous êtes Premium !</h2>
                    <p>Votre abonnement expire le <?php echo date('d/m/Y', strtotime($user['premium_expires_at'])); ?></p>
                </div>
                
                <div class="premium-actions">
                    <button class="btn-cancel-premium" onclick="showCancelModal()">
                        <i class="fas fa-times-circle"></i>
                        Annuler l'abonnement
                    </button>
                    <a href="profile.php" class="btn-manage">
                        <i class="fas fa-cog"></i>
                        Gérer mon profil
                    </a>
                </div>
            <?php else: ?>
                <h2>Compte Gratuit</h2>
                <p>Passez en Premium pour débloquer toutes les fonctionnalités</p>
            <?php endif; ?>
        </div>

        <!-- Section Hero -->
        <div class="hero-section">
            <div class="premium-crown">
                <i class="fas fa-crown"></i>
            </div>
            <h1 class="hero-title">Loove Premium</h1>
            <p class="hero-subtitle">
                Débloquez tout le potentiel de Loove avec notre abonnement Premium. 
                Plus de matches, plus de fonctionnalités, plus de chances de trouver l'amour !
            </p>
        </div>

        <!-- Fonctionnalités Premium -->
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-infinity"></i>
                </div>
                <h3 class="feature-title">Likes Illimités</h3>
                <p>Likez autant de profils que vous voulez sans restriction</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <h3 class="feature-title">Voir qui vous a liké</h3>
                <p>Découvrez qui s'intéresse à vous avant même de swiper</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-star"></i>
                </div>
                <h3 class="feature-title">Super Likes</h3>
                <p>5 Super Likes par jour pour vous démarquer</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <h3 class="feature-title">Boost de profil</h3>
                <p>Apparaissez en premier pendant 30 minutes</p>
            </div>
        </div>

        <!-- Plans d'abonnement -->
        <?php if (!$user['is_premium']): ?>
        <div class="plans-container">
            <div class="plan-card">
                <div class="plan-name">Hebdomadaire</div>
                <div class="plan-price">9,99€</div>
                <div class="plan-period">par semaine</div>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i> Likes illimités</li>
                    <li><i class="fas fa-check"></i> Voir qui vous a liké</li>
                    <li><i class="fas fa-check"></i> 5 Super Likes/jour</li>
                    <li><i class="fas fa-check"></i> 1 Boost/semaine</li>
                </ul>
                <button class="btn-select-plan" onclick="openPaymentModal('weekly', '9,99€')">
                    Choisir ce plan
                </button>
            </div>

            <div class="plan-card featured">
                <div class="plan-name">Mensuel</div>
                <div class="plan-price">29,99€</div>
                <div class="plan-period">par mois</div>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i> Likes illimités</li>
                    <li><i class="fas fa-check"></i> Voir qui vous a liké</li>
                    <li><i class="fas fa-check"></i> 5 Super Likes/jour</li>
                    <li><i class="fas fa-check"></i> 3 Boosts/mois</li>
                    <li><i class="fas fa-check"></i> Mode invisible</li>
                </ul>
                <button class="btn-select-plan" onclick="openPaymentModal('monthly', '29,99€')">
                    Choisir ce plan
                </button>
            </div>

            <div class="plan-card">
                <div class="plan-name">Annuel</div>
                <div class="plan-price">299,99€</div>
                <div class="plan-period">par an</div>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i> Likes illimités</li>
                    <li><i class="fas fa-check"></i> Voir qui vous a liké</li>
                    <li><i class="fas fa-check"></i> 5 Super Likes/jour</li>
                    <li><i class="fas fa-check"></i> Boosts illimités</li>
                    <li><i class="fas fa-check"></i> Mode invisible</li>
                    <li><i class="fas fa-check"></i> Badge exclusif</li>
                </ul>
                <button class="btn-select-plan" onclick="openPaymentModal('yearly', '299,99€')">
                    Choisir ce plan
                </button>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <!-- Modal de paiement -->
    <div class="payment-modal" id="paymentModal">
        <div class="payment-content">
            <div class="payment-header">
                <h2 class="payment-title">Finaliser votre abonnement</h2>
                <p id="selectedPlan"></p>
            </div>

            <form method="POST" id="paymentForm">
                <input type="hidden" name="purchase_premium" value="1">
                <input type="hidden" name="plan_type" id="planType">

                <div class="form-group">
                    <label class="form-label">Méthode de paiement</label>
                    <div class="payment-methods">
                        <div class="payment-method selected" onclick="selectPaymentMethod('card')" data-method="card">
                            <i class="fas fa-credit-card"></i>
                            <div>Carte</div>
                        </div>
                        <div class="payment-method" onclick="selectPaymentMethod('paypal')" data-method="paypal">
                            <i class="fab fa-paypal"></i>
                            <div>PayPal</div>
                        </div>
                        <div class="payment-method" onclick="selectPaymentMethod('apple')" data-method="apple">
                            <i class="fab fa-apple-pay"></i>
                            <div>Apple Pay</div>
                        </div>
                        <div class="payment-method" onclick="selectPaymentMethod('google')" data-method="google">
                            <i class="fab fa-google-pay"></i>
                            <div>Google Pay</div>
                        </div>
                    </div>
                    <input type="hidden" name="payment_method" id="paymentMethod" value="card">
                </div>

                <div id="cardDetails">
                    <div class="form-group">
                        <label class="form-label">Numéro de carte</label>
                        <input type="text" name="card_number" class="form-input" placeholder="1234 5678 9012 3456" maxlength="19">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label class="form-label">Expiration</label>
                            <input type="text" name="card_expiry" class="form-input" placeholder="MM/AA" maxlength="5">
                        </div>
                        <div class="form-group">
                            <label class="form-label">CVV</label>
                            <input type="text" name="card_cvv" class="form-input" placeholder="123" maxlength="3">
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 30px;">
                    <button type="button" class="btn-cancel" onclick="closePaymentModal()">
                        Annuler
                    </button>
                    <button type="submit" class="btn-pay">
                        <i class="fas fa-lock"></i> Payer maintenant
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal d'annulation -->
    <div class="cancel-modal" id="cancelModal">
        <div class="cancel-content">
            <div class="cancel-header">
                <h2 class="cancel-title">Annuler votre abonnement Premium</h2>
                <p>Êtes-vous sûr de vouloir annuler votre abonnement ?</p>
            </div>

            <div class="cancel-info">
                <div class="info-item">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Vous perdrez immédiatement :</strong>
                        <ul>
                            <li>Les likes illimités</li>
                            <li>La possibilité de voir qui vous a liké</li>
                            <li>Les Super Likes quotidiens</li>
                            <li>Les boosts de profil</li>
                        </ul>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Important :</strong>
                        L'annulation est immédiate et vous ne serez pas remboursé pour la période restante.
                    </div>
                </div>
            </div>

            <form method="POST" style="margin-top: 30px;">
                <div class="cancel-actions">
                    <button type="button" class="btn-keep-premium" onclick="closeCancelModal()">
                        <i class="fas fa-crown"></i>
                        Garder Premium
                    </button>
                    <button type="submit" name="cancel_premium" class="btn-confirm-cancel">
                        <i class="fas fa-times"></i>
                        Confirmer l'annulation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPaymentModal(planType, price) {
            document.getElementById('paymentModal').style.display = 'flex';
            document.getElementById('planType').value = planType;
            document.getElementById('selectedPlan').textContent = `Plan ${planType} - ${price}`;
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }

        function selectPaymentMethod(method) {
            document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('selected'));
            document.querySelector(`[data-method="${method}"]`).classList.add('selected');
            document.getElementById('paymentMethod').value = method;
            
            const cardDetails = document.getElementById('cardDetails');
            if (method === 'card') {
                cardDetails.style.display = 'block';
                // Rendre les champs obligatoires
                document.querySelector('input[name="card_number"]').required = true;
                document.querySelector('input[name="card_expiry"]').required = true;
                document.querySelector('input[name="card_cvv"]').required = true;
            } else {
                cardDetails.style.display = 'none';
                // Retirer l'obligation
                document.querySelector('input[name="card_number"]').required = false;
                document.querySelector('input[name="card_expiry"]').required = false;
                document.querySelector('input[name="card_cvv"]').required = false;
            }
        }

        // Validation du formulaire avant soumission
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const paymentMethod = document.getElementById('paymentMethod').value;
            
            if (paymentMethod === 'card') {
                const cardNumber = document.querySelector('input[name="card_number"]').value;
                const cardExpiry = document.querySelector('input[name="card_expiry"]').value;
                const cardCvv = document.querySelector('input[name="card_cvv"]').value;
                
                if (!cardNumber || !cardExpiry || !cardCvv) {
                    e.preventDefault();
                    alert('Veuillez remplir toutes les informations de carte bancaire.');
                    return false;
                }
                
                if (cardNumber.replace(/\s/g, '').length < 16) {
                    e.preventDefault();
                    alert('Le numéro de carte doit contenir au moins 16 chiffres.');
                    return false;
                }
                
                if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(cardExpiry)) {
                    e.preventDefault();
                    alert('Format de date d\'expiration invalide (MM/AA).');
                    return false;
                }
                
                if (cardCvv.length < 3) {
                    e.preventDefault();
                    alert('Le code CVV doit contenir 3 chiffres.');
                    return false;
                }
            }
        });

        function showCancelModal() {
            document.getElementById('cancelModal').style.display = 'flex';
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
        }

        // Formatage automatique des champs de carte
        document.querySelector('input[name="card_number"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            if (formattedValue.length > 19) formattedValue = formattedValue.substring(0, 19);
            e.target.value = formattedValue;
        });

        document.querySelector('input[name="card_expiry"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });

        document.querySelector('input[name="card_cvv"]').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });

        // Fermer le modal en cliquant à l'extérieur
        document.getElementById('paymentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePaymentModal();
            }
        });

        document.getElementById('cancelModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCancelModal();
            }
        });
    </script>
</body>
</html>
