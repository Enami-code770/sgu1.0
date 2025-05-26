<?php
// Fonctions principales du jeu
function create_new_game($conn, $user_id) {
    $stmt = $conn->prepare("INSERT INTO uno_games (creator_id) VALUES (?)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $game_id = $stmt->insert_id;
    $stmt->close();
    
    // Initialiser le deck
    initialize_deck($conn, $game_id);
    
    // Ajouter le créateur comme joueur
    add_player_to_game($conn, $game_id, $user_id, 0);
    
    $_SESSION['current_game'] = $game_id;
    return $game_id;
}

function join_game($conn, $game_id, $user_id) {
    // Vérifier si la partie existe et a de la place
    $stmt = $conn->prepare("SELECT COUNT(*) as player_count FROM uno_players WHERE game_id = ?");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['player_count'] < 4) { // Limite à 4 joueurs
        $player_order = $row['player_count'];
        add_player_to_game($conn, $game_id, $user_id, $player_order);
        $_SESSION['current_game'] = $game_id;
        return true;
    }
    
    $stmt->close();
    return false;
}

function add_player_to_game($conn, $game_id, $user_id, $player_order) {
    // Distribuer 7 cartes au joueur
    $cards = draw_cards($conn, $game_id, 7);
    
    $stmt = $conn->prepare("INSERT INTO uno_players 
                          (game_id, user_id, player_order, cards_in_hand) 
                          VALUES (?, ?, ?, ?)");
    $cards_json = json_encode($cards);
    $stmt->bind_param("iiis", $game_id, $user_id, $player_order, $cards_json);
    $stmt->execute();
    $stmt->close();
}

function draw_cards($conn, $game_id, $count = 1) {
    // Récupérer le deck
    $stmt = $conn->prepare("SELECT card_data FROM uno_decks WHERE game_id = ? AND is_discard = FALSE");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $deck = json_decode($row['card_data'], true);
    
    // Si le deck est vide, mélanger la défausse (sauf la dernière carte)
    if (count($deck) < $count) {
        reshuffle_discard($conn, $game_id);
        return draw_cards($conn, $game_id, $count); // Rappel récursif
    }
    
    // Piocher les cartes
    $drawn_cards = array_splice($deck, 0, $count);
    
    // Mettre à jour le deck
    $stmt = $conn->prepare("UPDATE uno_decks SET card_data = ? WHERE game_id = ? AND is_discard = FALSE");
    $new_deck_json = json_encode($deck);
    $stmt->bind_param("si", $new_deck_json, $game_id);
    $stmt->execute();
    $stmt->close();
    
    return $drawn_cards;
}

function reshuffle_discard($conn, $game_id) {
    // Récupérer la défausse (sauf la dernière carte)
    $stmt = $conn->prepare("SELECT card_data FROM uno_decks WHERE game_id = ? AND is_discard = TRUE");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $discard = json_decode($row['card_data'], true);
    
    // Garder la dernière carte
    $last_card = array_pop($discard);
    
    // Mélanger le reste
    shuffle($discard);
    
    // Mettre à jour la défausse (ne contient plus que la dernière carte)
    $stmt = $conn->prepare("UPDATE uno_decks SET card_data = ? WHERE game_id = ? AND is_discard = TRUE");
    $new_discard_json = json_encode([$last_card]);
    $stmt->bind_param("si", $new_discard_json, $game_id);
    $stmt->execute();
    
    // Remettre les cartes mélangées dans le deck
    $stmt = $conn->prepare("UPDATE uno_decks SET card_data = ? WHERE game_id = ? AND is_discard = FALSE");
    $new_deck_json = json_encode($discard);
    $stmt->bind_param("si", $new_deck_json, $game_id);
    $stmt->execute();
    $stmt->close();
}

function get_available_games($conn) {
    $games = [];
    $result = $conn->query("SELECT game_id, creator_id FROM uno_games WHERE game_status = 'waiting'");
    
    while ($row = $result->fetch_assoc()) {
        $games[] = $row;
    }
    
    return $games;
}

function get_game_state($conn, $game_id) {
    $stmt = $conn->prepare("SELECT * FROM uno_games WHERE game_id = ?");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function get_player_hand($conn, $game_id, $user_id) {
    $stmt = $conn->prepare("SELECT cards_in_hand FROM uno_players WHERE game_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $game_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return json_decode($row['cards_in_hand'], true);
}
?>