<?php
session_start();
require_once 'classes/Message.php';

header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$search_term = trim($input['search']);

$messageSystem = new Message();
$users = $messageSystem->searchUsers($search_term, $_SESSION["user_id"]);

echo json_encode(['users' => $users]);
?>
