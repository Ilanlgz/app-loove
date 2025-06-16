<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once 'config/database.php';
require_once 'classes/Match.php';
require_once 'classes/Report.php';

$conn = getDbConnection();
$matchSystem = new MatchSystem();
$reportSystem = new Report();

// Récupérer les filtres
$age_min = isset($_GET['age_min']) ? (int)$_GET['age_min'] : 18;
$age_max = isset($_GET['age_max']) ? (int)$_GET['age_max'] : 99;
$distance = isset($_GET['distance']) ? (int)$_GET['distance'] : 50;
$interests = isset($_GET['interests']) ? $_GET['interests'] : '';

// Récupérer les utilisateurs avec filtres
$discover_users = $matchSystem->getDiscoverUsersWithFilters($_SESSION["user_id"], $age_min, $age_max, $distance, $interests);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Découvrir - Loove</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- ...existing code CSS... -->
    
    <style>
        .filters-panel {
            background: var(--white);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .filters-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        .filters-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-label {
            font-weight: 500;
            margin-bottom: 5px;
            color: var(--text-primary);
        }
        
        .filter-input {
            padding: 10px;
            border: 2px solid #E8E8E8;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .btn-apply-filters {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- ...existing code header... -->

    <main class="main-content">
        <!-- Panneau de filtres -->
        <div class="filters-panel">
            <h3 class="filters-title">
                <i class="fas fa-filter"></i> Filtres de recherche
            </h3>
            
            <form method="GET" action="">
                <div class="filters-row">
                    <div class="filter-group">
                        <label class="filter-label">Âge minimum</label>
                        <input type="number" name="age_min" class="filter-input" value="<?php echo $age_min; ?>" min="18" max="99">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Âge maximum</label>
                        <input type="number" name="age_max" class="filter-input" value="<?php echo $age_max; ?>" min="18" max="99">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Distance (km)</label>
                        <input type="number" name="distance" class="filter-input" value="<?php echo $distance; ?>" min="1" max="500">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Centres d'intérêt</label>
                        <input type="text" name="interests" class="filter-input" value="<?php echo htmlspecialchars($interests); ?>" placeholder="Ex: Sport, Musique">
                    </div>
                </div>
                
                <button type="submit" class="btn-apply-filters">
                    <i class="fas fa-search"></i> Appliquer les filtres
                </button>
            </form>
        </div>

        <!-- ...existing code discover cards... -->
    </main>
</body>
</html>
