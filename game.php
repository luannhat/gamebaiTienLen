<?php
header('Content-Type: application/json');

function createDeck() {
    $suits = ['♠', '♣', '♦', '♥'];
    $values = ['3','4','5','6','7','8','9','10','J','Q','K','A','2'];
    $deck = [];

    foreach ($suits as $suit) {
        foreach ($values as $value) {
            $deck[] = ['value' => $value, 'suit' => $suit];
        }
    }

    shuffle($deck);
    return $deck;
}

if ($_GET['action'] === 'deal') {
    $deck = createDeck();
    $player = array_slice($deck, 0, 13);
    $bot = array_slice($deck, 13, 13);
    echo json_encode([
        'player' => $player,
        'bot' => $bot
    ]);
}

if ($_GET['action'] === 'play') {
    $data = json_decode(file_get_contents('php://input'), true);
    $played = $data['cards'];
    echo json_encode(['played' => $played]);
}
