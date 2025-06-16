<?php
require_once BASE_PATH . '/app/controllers/BaseController.php';
require_once BASE_PATH . '/app/models/User.php';

class PremiumController extends BaseController {
    private $userModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->userModel = new User();
    }
    
    public function index() {
        $user = $this->userModel->findById($_SESSION['user_id']);
        
        $this->view('premium/index', [
            'title' => 'Premium - Loove',
            'user' => $user,
            'success' => $this->getFlash('success'),
            'error' => $this->getFlash('error')
        ]);
    }
    
    public function purchase() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/loove/public/premium');
        }
        
        $planType = $_POST['plan_type'] ?? '';
        $paymentMethod = $_POST['payment_method'] ?? '';
        $cardNumber = $_POST['card_number'] ?? '';
        
        // Validation basique des cartes de test
        $validTestCards = [
            '4111111111111111',
            '5555555555554444',
            '4000000000000002'
        ];
        
        $cardClean = str_replace(' ', '', $cardNumber);
        
        if (in_array($cardClean, $validTestCards)) {
            // Simuler l'activation Premium
            $this->userModel->activatePremium($_SESSION['user_id'], $planType);
            $this->setFlash('success', 'Félicitations ! Votre abonnement Premium a été activé !');
        } else {
            $this->setFlash('error', 'Paiement refusé. Utilisez une carte de test valide.');
        }
        
        $this->redirect('/loove/public/premium');
    }
}
?>
