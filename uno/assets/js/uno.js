// Fonctions principales
function createGame() {
    fetch('../jeu.php?action=create')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
}

function joinGame(gameId) {
    fetch(`../jeu.php?action=join&game_id=${gameId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
}

// Mettre à jour l'état du jeu régulièrement
function updateGameState() {
    if (!document.querySelector('.game-board')) return;

    fetch('../ajax/get_game_state.php')
        .then(response => response.json())
        .then(data => {
            updateGameUI(data);
            setTimeout(updateGameState, 3000); // Rafraîchir toutes les 3 secondes
        });
}

function updateGameUI(gameState) {
    // Mettre à jour l'interface utilisateur avec l'état du jeu
    document.getElementById('game-status').innerHTML = `
        <p>Tour du joueur ${gameState.current_player + 1}</p>
        <p>Carte actuelle: <span class="card ${gameState.current_color}">${gameState.current_value}</span></p>
    `;

    // Afficher la défausse
    document.getElementById('discard-pile').innerHTML = `
        <div class="card ${gameState.current_color}">${gameState.current_value}</div>
    `;
}

// Démarrer la mise à jour de l'état du jeu
document.addEventListener('DOMContentLoaded', function() {
    updateGameState();
});