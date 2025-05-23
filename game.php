<?php
session_start();
$data = json_decode(file_get_contents("php://input"), true);
$index = $data['index'];

if (!isset($_SESSION['player'][$index])) {
    echo json_encode(['message' => 'Lá bài không hợp lệ.', 'reload' => false]);
    exit;
}

// Lấy bài người chơi đánh
$card = $_SESSION['player'][$index];
unset($_SESSION['player'][$index]);
$_SESSION['player'] = array_values($_SESSION['player']); // reindex

// Giả lập lượt bot
$botCard = array_pop($_SESSION['bot']);

echo json_encode([
    'message' => "Bạn đánh: {$card['value']}{$card['suit']}, Bot đánh: {$botCard['value']}{$botCard['suit']}",
    'reload' => true
]);
