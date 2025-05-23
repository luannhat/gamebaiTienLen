<?php
session_start();
include 'cards.php';

// Khởi tạo bài nếu chưa có
if (!isset($_SESSION['player']) || !isset($_SESSION['bot'])) {
    $deck = createDeck();
    $_SESSION['player'] = array_slice($deck, 0, 13);
    $_SESSION['bot'] = array_slice($deck, 13, 13);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tiến Lên Đơn Giản</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Tiến Lên - Solo với Bot</h1>

    <div id="player-hand">
        <?php foreach ($_SESSION['player'] as $index => $card): ?>
            <div class="card" onclick="playCard(<?= $index ?>)">
                <?= $card['value'] . $card['suit'] ?>
            </div>
        <?php endforeach; ?>
    </div>

    <p id="message"></p>

    <script src="game.js"></script>
</body>
</html>
