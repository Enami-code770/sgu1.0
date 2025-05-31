<?php
require_once 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['current_game'])) {
    echo json_encode(['error' => 'Aucune partie en cours']);
    exit;
}

$game_id = $_SESSION['current_game'];
$game_state = get_game_state($conn, $game_id);

// Récupérer les joueurs
$players = [];
$result = $conn->query("SELECT * FROM uno_players WHERE game_id = $game_id");
while ($row = $result->fetch_assoc()) {
    $players[] = $row;
}

// Récupérer la main du joueur actuel
$player_hand = [];
$result = $conn->query("SELECT cards_in_hand FROM uno_players WHERE game_id = $game_id AND user_id = {$_SESSION['user_id']}");
if ($row = $result->fetch_assoc()) {
    $player_hand = json_decode($row['cards_in_hand'], true);
}

echo json_encode([
    'game' => $game_state,
    'players' => $players,
    'player_hand' => $player_hand
]);
?>