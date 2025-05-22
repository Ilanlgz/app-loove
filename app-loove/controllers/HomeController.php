<?php
class HomeController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->addCss('/css/home.css');
        $this->addJs('/js/home.js');
    }
    
    public function handleRequest() {
        // Pour la page d'accueil, on n'oblige pas la connexion
        // mais on adapte l'affichage selon que l'utilisateur est connecté ou non
        $isLoggedIn = isset($_SESSION['user_id']);
        
        $this->setTitle('Accueil - Loove Dating App');
        
        // Utiliser uniquement des données de démo pour éviter les erreurs de tables inexistantes
        $stories = $this->generateDemoStories();
        $posts = $this->generateDemoPosts();
        $popularUsers = $this->getPopularUsers(); // Cette fonction vérifie si la table users existe
        
        $this->render('home/index', [
            'isLoggedIn' => $isLoggedIn,
            'stories' => $stories,
            'posts' => $posts,
            'popularUsers' => $popularUsers,
            'currentPage' => 'home'
        ]);
    }
    
    private function getRecentStories() {
        // Éviter d'essayer d'accéder à des tables non existantes
        // et utiliser directement les données de démo
        return $this->generateDemoStories();
    }
    
    private function getRecentPosts() {
        // Éviter d'essayer d'accéder à des tables non existantes
        // et utiliser directement les données de démo
        return $this->generateDemoPosts();
    }
    
    private function getPopularUsers() {
        $db = getDatabaseConnection();
        $users = [];
        
        // Vérifier d'abord si la table users existe
        $tableExists = false;
        try {
            $checkTable = $db->query("SHOW TABLES LIKE 'users'");
            $tableExists = ($checkTable && $checkTable->num_rows > 0);
        } catch (Exception $e) {
            $tableExists = false;
        }
        
        // Si la table existe, récupérer les utilisateurs
        if ($tableExists) {
            try {
                $sql = "SELECT u.id, u.name, u.profile_picture, u.bio, 
                        TIMESTAMPDIFF(YEAR, u.birthdate, CURDATE()) as age
                    FROM users u
                    ORDER BY RAND()
                    LIMIT 4";
                
                $result = $db->query($sql);
                
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $users[] = $row;
                    }
                }
            } catch (Exception $e) {
                // En cas d'erreur, utiliser les données de démo
                $users = $this->generateDemoUsers();
            }
        }
        
        // Si pas d'utilisateurs récupérés, utiliser les données de démo
        if (count($users) < 4) {
            $demoUsers = $this->generateDemoUsers();
            $users = array_merge($users, $demoUsers);
            // Limiter à 4 utilisateurs
            $users = array_slice($users, 0, 4);
        }
        
        $db->close();
        return $users;
    }
    
    // Méthodes pour générer des données de démo
    private function generateDemoStories() {
        $demoStories = [];
        $demoUsers = [
            ['id' => 101, 'name' => 'Emma Martin', 'profile_picture' => 'demo-user-1.jpg'],
            ['id' => 102, 'name' => 'Thomas Dubois', 'profile_picture' => 'demo-user-2.jpg'],
            ['id' => 103, 'name' => 'Sophie Laurent', 'profile_picture' => 'demo-user-3.jpg'],
            ['id' => 104, 'name' => 'Lucas Bernard', 'profile_picture' => 'demo-user-4.jpg'],
            ['id' => 105, 'name' => 'Chloé Moreau', 'profile_picture' => 'demo-user-5.jpg']
        ];
        
        $demoImages = ['story-1.jpg', 'story-2.jpg', 'story-3.jpg', 'story-4.jpg', 'story-5.jpg'];
        
        for ($i = 0; $i < 5; $i++) {
            $demoStories[] = [
                'id' => 1000 + $i,
                'user_id' => $demoUsers[$i]['id'],
                'name' => $demoUsers[$i]['name'],
                'profile_picture' => $demoUsers[$i]['profile_picture'],
                'image' => $demoImages[$i],
                'created_at' => date('Y-m-d H:i:s', time() - rand(1, 24) * 3600)
            ];
        }
        
        return $demoStories;
    }
    
    private function generateDemoPosts() {
        $demoPosts = [];
        $demoUsers = [
            ['id' => 101, 'name' => 'Emma Martin', 'profile_picture' => 'demo-user-1.jpg'],
            ['id' => 102, 'name' => 'Thomas Dubois', 'profile_picture' => 'demo-user-2.jpg'],
            ['id' => 103, 'name' => 'Sophie Laurent', 'profile_picture' => 'demo-user-3.jpg'],
            ['id' => 104, 'name' => 'Lucas Bernard', 'profile_picture' => 'demo-user-4.jpg'],
            ['id' => 105, 'name' => 'Chloé Moreau', 'profile_picture' => 'demo-user-5.jpg']
        ];
        
        $demoImages = ['post-1.jpg', 'post-2.jpg', 'post-3.jpg', 'post-4.jpg', 'post-5.jpg', 'post-6.jpg', 'post-7.jpg', 'post-8.jpg'];
        $captions = [
            'Profiter des beaux jours ☀️ #weekend',
            'Une belle journée à la plage 🌊',
            'Après-midi entre amis 💕',
            'Soirée parfaite 🍹',
            'Nouvelle tenue, nouveau moi 💯',
            'Toujours en mouvement 🏃‍♂️',
            'Moments de détente ✨',
            'Voyage inoubliable 🌍'
        ];
        
        for ($i = 0; $i < 8; $i++) {
            $user = $demoUsers[rand(0, 4)];
            $demoPosts[] = [
                'id' => 2000 + $i,
                'user_id' => $user['id'],
                'name' => $user['name'],
                'profile_picture' => $user['profile_picture'],
                'image' => $demoImages[$i],
                'caption' => $captions[$i],
                'likes_count' => rand(5, 150),
                'comments_count' => rand(0, 30),
                'created_at' => date('Y-m-d H:i:s', time() - rand(1, 72) * 3600)
            ];
        }
        
        return $demoPosts;
    }
    
    private function generateDemoUsers() {
        return [
            [
                'id' => 101,
                'name' => 'Emma Martin',
                'profile_picture' => 'demo-user-1.jpg',
                'bio' => 'Passionnée de voyage et de photographie. Toujours à la recherche de nouvelles aventures !',
                'age' => 28
            ],
            [
                'id' => 102,
                'name' => 'Thomas Dubois',
                'profile_picture' => 'demo-user-2.jpg',
                'bio' => 'Sportif, musicien à mes heures perdues. J\'aime les longues balades et les bonnes conversations.',
                'age' => 31
            ],
            [
                'id' => 103,
                'name' => 'Sophie Laurent',
                'profile_picture' => 'demo-user-3.jpg',
                'bio' => 'Artiste dans l\'âme, amoureuse des animaux et de la nature. À la recherche d\'authenticité.',
                'age' => 26
            ],
            [
                'id' => 104,
                'name' => 'Lucas Bernard',
                'profile_picture' => 'demo-user-4.jpg',
                'bio' => 'Entrepreneur, passionné de cuisine et de vin. J\'aime découvrir de nouveaux restaurants.',
                'age' => 33
            ]
        ];
    }
    
    // Helper pour vérifier si une table existe
    private function tableExists($db, $tableName) {
        try {
            $result = $db->query("SHOW TABLES LIKE '$tableName'");
            return $result && $result->num_rows > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Convertit un timestamp en format "il y a X temps"
     * 
     * @param int $timestamp Timestamp à convertir
     * @return string Texte formaté (ex: "il y a 2 heures")
     */
    public function timeAgo($timestamp) {
        $time_diff = time() - $timestamp;
        
        if ($time_diff < 60) {
            return 'à l\'instant';
        } elseif ($time_diff < 3600) {
            $minutes = floor($time_diff / 60);
            return 'il y a ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
        } elseif ($time_diff < 86400) {
            $hours = floor($time_diff / 3600);
            return 'il y a ' . $hours . ' heure' . ($hours > 1 ? 's' : '');
        } elseif ($time_diff < 604800) {
            $days = floor($time_diff / 86400);
            return 'il y a ' . $days . ' jour' . ($days > 1 ? 's' : '');
        } elseif ($time_diff < 2592000) {
            $weeks = floor($time_diff / 604800);
            return 'il y a ' . $weeks . ' semaine' . ($weeks > 1 ? 's' : '');
        } else {
            return date('j M Y', $timestamp);
        }
    }
}
