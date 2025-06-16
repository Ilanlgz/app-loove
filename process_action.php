<?php
session_start();
require_once 'classes/Match.php';

header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'];
$target_user_id = $input['user_id'];
$current_user_id = $_SESSION["user_id"];

$matchSystem = new MatchSystem();

if ($action === 'like') {
    $result = $matchSystem->likeUser($current_user_id, $target_user_id);
} else if ($action === 'pass') {
    $result = $matchSystem->passUser($current_user_id, $target_user_id);
} else {
    echo json_encode(['error' => 'Invalid action']);
    exit;
}

echo json_encode(['result' => $result]);
?>
