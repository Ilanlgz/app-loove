<?php
require_once 'config/database.php';

function generateRandomProfile($force_gender = '') {
    $first_names_male = ['Alexandre', 'Julien', 'Thomas', 'Nicolas', 'Antoine', 'Pierre', 'Lucas', 'Mathieu', 'Maxime', 'Kevin', 'Romain', 'Hugo', 'David', 'Florian', 'Quentin', 'Paul', 'Louis', 'Gabriel', 'Arthur', 'Nathan'];
    $first_names_female = ['Emma', 'Jade', 'Louise', 'Alice', 'Chloé', 'Lina', 'Léa', 'Manon', 'Julia', 'Zoé', 'Camille', 'Sarah', 'Marie', 'Clara', 'Inès', 'Eva', 'Romane', 'Lisa', 'Anaïs', 'Anna'];
    $last_names = ['Martin', 'Bernard', 'Thomas', 'Petit', 'Robert', 'Richard', 'Durand', 'Dubois', 'Moreau', 'Laurent', 'Simon', 'Michel', 'Lefebvre', 'Leroy', 'Roux', 'David', 'Bertrand', 'Morel', 'Fournier', 'Girard'];
    
    $cities = ['Paris', 'Lyon', 'Marseille', 'Toulouse', 'Nice', 'Nantes', 'Strasbourg', 'Montpellier', 'Bordeaux', 'Lille', 'Rennes', 'Reims', 'Le Havre', 'Saint-Étienne', 'Toulon', 'Grenoble', 'Dijon', 'Angers', 'Villeurbanne', 'Le Mans'];
    
    $occupations = ['Développeur', 'Designer', 'Médecin', 'Avocat', 'Professeur', 'Ingénieur', 'Chef', 'Photographe', 'Artiste', 'Entrepreneur', 'Marketing', 'Commercial', 'Infirmier/ère', 'Architecte', 'Journaliste', 'Psychologue', 'Comptable', 'Pharmacien/ne', 'Dentiste', 'Étudiant/e'];
    
    $bios = [
        "Passionné de voyages et de découvertes. J'aime les soirées entre amis et les bons restaurants.",
        "Amoureuse de la nature et des randonnées. À la recherche de quelqu'un pour partager de beaux moments.",
        "Sportif dans l'âme, j'adore le fitness et les activités en plein air. Fan de cinéma aussi !",
        "Créatif et curieux, j'aime l'art, la musique et les expositions. Toujours partant pour de nouvelles aventures.",
        "Épicurien qui aime profiter de la vie. Cuisine, vin et bonne compagnie sont mes péchés mignons.",
        "Aventurier urbain, j'aime découvrir les spots cachés de la ville. Fan de street-art et de concerts.",
        "Bibliophile et cinéphile, j'adore les discussions profondes autour d'un bon café.",
        "Positive et souriante, j'aime rire et faire rire. À la recherche de complicité et d'authenticité.",
    ];
    
    $interests_list = [
        'Voyage,Cuisine,Cinéma,Sport',
        'Lecture,Musique,Art,Photographie',
        'Randonnée,Nature,Yoga,Méditation',
        'Fitness,Danse,Théâtre,Mode',
        'Gaming,Technologie,Science,Innovation',
        'Food,Vin,Cocktails,Gastronomie',
        'Concerts,Festivals,Vinyle,Guitare',
        'Ski,Escalade,Surf,Parapente'
    ];
    
    $relationship_statuses = ['single', 'divorced', 'separated'];
    
    $gender = $force_gender ?: (rand(0, 1) ? 'male' : 'female');
    $first_name = $gender === 'male' ? $first_names_male[array_rand($first_names_male)] : $first_names_female[array_rand($first_names_female)];
    $last_name = $last_names[array_rand($last_names)];
    
    $age = rand(18, 45);
    $birth_year = date('Y') - $age;
    $birth_month = rand(1, 12);
    $birth_day = rand(1, 28);
    $date_of_birth = "$birth_year-$birth_month-$birth_day";
    
    return [
        'email' => strtolower($first_name . '.' . $last_name . rand(100, 999) . '@example.com'),
        'password' => password_hash('demo123', PASSWORD_DEFAULT),
        'first_name' => $first_name,
        'last_name' => $last_name,
        'date_of_birth' => $date_of_birth,
        'age' => $age,
        'gender' => $gender,
        'location' => $cities[array_rand($cities)],
        'occupation' => $occupations[array_rand($occupations)],
        'bio' => $bios[array_rand($bios)],
        'interests' => $interests_list[array_rand($interests_list)],
        'height' => rand(150, 200),
        'relationship_status' => $relationship_statuses[array_rand($relationship_statuses)],
        'is_active' => 1,
        'is_premium' => rand(0, 4) === 0 ? 1 : 0, // 20% de chance d'être premium
        'last_active' => date('Y-m-d H:i:s', strtotime('-' . rand(0, 1440) . ' minutes'))
    ];
}

function insertGeneratedProfile($conn, $profile) {
    $query = "INSERT INTO users (email, password, first_name, last_name, date_of_birth, age, gender, location, occupation, bio, interests, height, relationship_status, is_active, is_premium, last_active, created_at) 
              VALUES (:email, :password, :first_name, :last_name, :date_of_birth, :age, :gender, :location, :occupation, :bio, :interests, :height, :relationship_status, :is_active, :is_premium, :last_active, NOW())";
    
    $stmt = $conn->prepare($query);
    return $stmt->execute($profile);
}

// Générer des profils si appelé directement
if (basename($_SERVER['PHP_SELF']) === 'generate_profiles.php') {
    try {
        $conn = getDbConnection();
        $generated = 0;
        
        for ($i = 0; $i < 50; $i++) {
            $profile = generateRandomProfile();
            if (insertGeneratedProfile($conn, $profile)) {
                $generated++;
            }
        }
        
        echo "✅ $generated profils générés avec succès !<br>";
        echo "<a href='discover.php'>🚀 Tester la découverte</a>";
        
    } catch (Exception $e) {
        echo "❌ Erreur : " . $e->getMessage();
    }
}
?>
