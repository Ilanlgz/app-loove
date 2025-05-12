<?php
class SearchController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->addCss('/assets/css/search.css');
    }
    
    public function handleRequest() {
        $this->requireLogin();
        
        $request_uri = $_SERVER['REQUEST_URI'];
        
        if (strpos($request_uri, '/search/results') !== false) {
            $this->showResults();
        } else {
            $this->showForm();
        }
    }
    
    private function showForm() {
        $this->setTitle('Recherche - Loove Dating App');
        $this->render('search/form', []);
    }
    
    private function showResults() {
        $this->setTitle('Résultats de recherche - Loove Dating App');
        
        // Paramètres de recherche
        $ageMin = isset($_GET['age_min']) ? intval($_GET['age_min']) : 18;
        $ageMax = isset($_GET['age_max']) ? intval($_GET['age_max']) : 99;
        $gender = isset($_GET['gender']) ? $_GET['gender'] : null;
        
        $db = getDatabaseConnection();
        $searchResults = [];
        $currentUserId = $_SESSION['user_id'];
        
        $sql = "SELECT u.id, u.name, u.profile_picture, u.bio, 
                TIMESTAMPDIFF(YEAR, u.birthdate, CURDATE()) AS age
                FROM users u
                WHERE u.id != ?";
        
        $params = [$currentUserId];
        $types = "i";
        
        // Filtres
        if ($gender && $gender != 'all') {
            $sql .= " AND u.gender = ?";
            $params[] = $gender;
            $types .= "s";
        }
        
        if ($ageMin > 0) {
            $sql .= " AND TIMESTAMPDIFF(YEAR, u.birthdate, CURDATE()) >= ?";
            $params[] = $ageMin;
            $types .= "i";
        }
        
        if ($ageMax < 99) {
            $sql .= " AND TIMESTAMPDIFF(YEAR, u.birthdate, CURDATE()) <= ?";
            $params[] = $ageMax;
            $types .= "i";
        }
        
        $sql .= " ORDER BY u.created_at DESC LIMIT 50";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $searchResults[] = $row;
        }
        
        $db->close();
        
        $this->render('search/results', [
            'searchResults' => $searchResults
        ]);
    }
}
?>