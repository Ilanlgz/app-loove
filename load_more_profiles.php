<?php
session_start();
require_once 'classes/Match.php';

header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$offset = isset($input['offset']) ? (int)$input['offset'] : 0;
$limit = isset($input['limit']) ? (int)$input['limit'] : 10;

$matchSystem = new MatchSystem();

// Obtenir de nouveaux profils
$profiles = $matchSystem->getDiscoverUsers($_SESSION["user_id"], $limit, $offset);

// Ne plus générer automatiquement de profils
// Retourner seulement les vrais utilisateurs disponibles

echo json_encode([
    'profiles' => $profiles,
    'count' => count($profiles),
    'real_users_only' => true
]);
?>
