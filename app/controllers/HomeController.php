<?php
namespace App\Controllers;

use Controller; // Assuming Controller is in the global namespace or autoloaded from core

class HomeController extends Controller {

    public function __construct(){
        parent::__construct();
    }

    public function index() {
        // The main authenticated content is now served by UserController@dashboard.
        // Redirect any access to this old route to the dashboard.
        $this->ensureLoggedIn(); // Ensure user is logged in before redirecting
        $this->redirect('dashboard'); 
    }
}
