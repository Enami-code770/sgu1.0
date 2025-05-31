<?php
function initialize_deck($conn, $game_id) {
    $colors = ['red', 'blue', 'green', 'yellow'];
    $values = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'skip', 'reverse', 'draw2'];
    $specials = ['wild', 'wild_draw4'];
    
    $deck = [];
    
    // Ajouter les cartes normales (2 de chaque sauf 0)
    foreach ($colors as $color) {
        foreach ($values as $value) {
            $deck[] = "$color,$value";
            if ($value !== '0') $deck[] = "$color,$value";
        }
    }
    
    // Ajouter les cartes spéciales (4 de chaque)
    for ($i = 0; $i < 4; $i++) {
        foreach ($specials as $special) {
            $deck[] = "wild,$special";
        }
    }
    
    // Mélanger le deck
    shuffle($deck);
    
    // Sauvegarder dans la base de données
    $json_deck = json_encode($deck);
    $stmt = $conn->prepare("INSERT INTO uno_decks (game_id, card_data) VALUES (?, ?)");
    $stmt->bind_param("is", $game_id, $json_deck);
    $stmt->execute();
    $stmt->close();
    
    // Tirer la première carte pour la défausse
    $first_card = array_pop($deck);
    $json_deck = json_encode($deck);
    
    // Mettre à jour le deck et créer la défausse
    $conn->query("UPDATE uno_decks SET card_data = '$json_deck' WHERE game_id = $game_id AND is_discard = FALSE");
    
    $stmt = $conn->prepare("INSERT INTO uno_decks (game_id, card_data, is_discard) VALUES (?, ?, TRUE)");
    $first_card_json = json_encode([$first_card]);
    $stmt->bind_param("is", $game_id, $first_card_json);
    $stmt->execute();
    $stmt->close();
    
    // Mettre à jour l'état du jeu avec la première carte
    list($color, $value) = explode(',', $first_card);
    $conn->query("UPDATE uno_games SET current_color = '$color', current_value = '$value' WHERE game_id = $game_id");
}
?>