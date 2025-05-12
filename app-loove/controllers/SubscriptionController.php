<?php
class SubscriptionController {
    private $subscriptionModel;

    public function __construct() {
        // Include the Subscription model
        require_once '../models/Subscription.php';
        $this->subscriptionModel = new Subscription();
    }

    public function viewPlans() {
        // Fetch subscription plans from the model
        $plans = $this->subscriptionModel->getAllPlans();
        // Include the view for displaying plans
        require_once '../views/subscription/plans.php';
    }

    public function subscribe($userId, $planId) {
        // Process subscription for the user
        if ($this->subscriptionModel->subscribeUser($userId, $planId)) {
            // Redirect or show success message
            header('Location: ../public/index.php?message=Subscription successful');
        } else {
            // Handle error
            header('Location: ../public/index.php?error=Subscription failed');
        }
    }

    public function cancelSubscription($userId) {
        // Cancel the user's subscription
        if ($this->subscriptionModel->cancelUserSubscription($userId)) {
            // Redirect or show success message
            header('Location: ../public/index.php?message=Subscription cancelled');
        } else {
            // Handle error
            header('Location: ../public/index.php?error=Cancellation failed');
        }
    }
}
?>