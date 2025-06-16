<?php
/**
 * Search Controller - Handles user search functionality
 */
class SearchController extends BaseController {
    private $user_model;
    
    public function __construct() {
        parent::__construct();
        $this->user_model = new UserModel();
        
        // Add search-specific CSS
        $this->addStyle('/css/search.css');
        
        // Require login for all search actions
        $this->requireLogin();
    }
    
    // Default action - show search form and results
    public function index() {
        $this->setTitle('Search');
        
        // Process search form
        $criteria = [];
        $results = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['search'])) {
            // Get search criteria
            $criteria = [
                'gender' => $_GET['gender'] ?? '',
                'min_age' => $_GET['min_age'] ?? '',
                'max_age' => $_GET['max_age'] ?? '',
                'location' => $_GET['location'] ?? ''
            ];
            
            // Get search results
            $results = $this->user_model->search($criteria);
        }
        
        $this->render('search/index', [
            'criteria' => $criteria,
            'results' => $results
        ]);
    }
    
    // Browse users
    public function browse() {
        $this->setTitle('Browse');
        
        // Get page parameters
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;
        
        // Get current user preferences
        $user_id = $_SESSION['user_id'];
        $user = $this->user_model->findById($user_id);
        
        // Set criteria based on preferences
        $criteria = [];
        if ($user['preference'] !== 'both') {
            $criteria['gender'] = $user['preference'];
        }
        
        // Get users
        $results = $this->user_model->search($criteria, $limit, $offset);
        
        // Get total count for pagination
        $total = $this->user_model->count();
        $total_pages = ceil($total / $limit);
        
        $this->render('search/browse', [
            'results' => $results,
            'page' => $page,
            'total_pages' => $total_pages
        ]);
    }
    
    // Discover (card swiping)
    public function discover() {
        $this->setTitle('Discover');
        
        $user_id = $_SESSION['user_id'];
        
        // Get potential matches
        $potential_matches = $this->user_model->findMatchesByPreference($user_id, 10);
        
        $this->render('search/discover', [
            'potential_matches' => $potential_matches
        ]);
    }
}
