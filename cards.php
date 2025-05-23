<?php
function createDeck() {
    $suits = ['♠', '♥', '♦', '♣'];
    $values = ['3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A', '2'];
    $deck = [];

    foreach ($suits as $suit) {
        foreach ($values as $value) {
            $deck[] = ['suit' => $suit, 'value' => $value];
        }
    }

    shuffle($deck);
    return $deck;
}
