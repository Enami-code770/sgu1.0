<?php
require_once '../includes/db.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?error=not_logged_in');
    exit();
}

// Inclure les composants du jeu
require_once 'includes/game.inc.php';
require_once 'includes/deck.inc.php';

// Gérer les actions
$action = $_GET['action'] ?? '';
$game_id = $_GET['game_id'] ?? 0;

switch ($action) {
    case 'create':
        create_new_game($conn, $_SESSION['user_id']);
        break;
    case 'join':
        join_game($conn, $game_id, $_SESSION['user_id']);
        break;
    case 'play':
        // Logique pour jouer une carte
        break;
}

// Récupérer les parties disponibles
$available_games = get_available_games($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>UNO - AquaServ</title>
    <link rel="stylesheet" href="assets/css/uno.css">
</head>
<body>
    <div class="uno-container">
        <h1>Jeu UNO</h1>
        
        <?php if (!isset($_SESSION['current_game'])): ?>
            <div class="game-lobby">
                <h2>Créer ou rejoindre une partie</h2>
                <button onclick="createGame()">Créer une partie</button>
                
                <h3>Parties disponibles</h3>
                <div id="available-games">
                    <?php foreach ($available_games as $game): ?>
                        <div class="game-item">
                            Partie #<?= $game['game_id'] ?> 
                            <button onclick="joinGame(<?= $game['game_id'] ?>)">Rejoindre</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="game-board">
                <!-- Interface du jeu -->
                <div id="game-status"></div>
                <div id="discard-pile"></div>
                <div id="players-container"></div>
                <div id="player-hand"></div>
            </div>
        <?php endif; ?>
    </div>

    <script src="assets/js/uno.js"></script>
</body>
</html>