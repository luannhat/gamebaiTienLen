<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Game Tiến Lên Miền Nam - Bot đánh bài</title>
    <style>
        .card.selected {
            transform: translateY(-15px);
            border: 2px solid #2196f3;
            box-shadow: 0 6px 12px rgba(33, 150, 243, 0.5);
        }
    </style>
</head>

<body>
    <h1>Game Tiến Lên Miền Nam</h1>
    <div class="game-table">
        <div class="status" id="game-status">Bấm "Chia bài" để bắt đầu</div>

        <div class="bot-hand" id="bot-hand"></div>
        <div class="played-cards" id="played-cards"></div>
        <div class="player-hand" id="player-hand"></div>

        <button class="btn" id="deal-btn" onclick="dealCards()">Chia bài</button>
        <button class="btn" id="play-btn" onclick="playSelectedCards()">Đánh</button>

    </div>

    <script>
        const ranks = ['3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A', '2'];
        const suits = [{
                symbol: '♠',
                name: 'spades'
            },
            {
                symbol: '♥',
                name: 'hearts'
            },
            {
                symbol: '♦',
                name: 'diamonds'
            },
            {
                symbol: '♣',
                name: 'clubs'
            }
        ];

        let isFirstTurn = true;

        let selectedCards = [];
        let lastPlayedCards = null;


        let playerHand = [],
            botHand = [],
            isPlayerTurn = true;

        function getRankIndex(rank) {
            return ranks.indexOf(rank);
        }

        function isConsecutive(arr) {
            for (let i = 1; i < arr.length; i++) {
                if (arr[i] !== arr[i - 1] + 1) return false;
            }
            return true;
        }

        function classifyCards(cards) {
            if (!cards || cards.length === 0) return null;
            cards.sort((a, b) => getRankIndex(a.rank) - getRankIndex(b.rank));
            const len = cards.length;
            const ranksIndices = cards.map(c => getRankIndex(c.rank));

            if (len === 1) return {
                type: 'single',
                rank: ranksIndices[0]
            };
            if (len === 2 && cards[0].rank === cards[1].rank)
                return {
                    type: 'pair',
                    rank: ranksIndices[0]
                };
            if (len === 4 && cards.every(c => c.rank === cards[0].rank))
                return {
                    type: 'bomb',
                    rank: ranksIndices[0]
                };
            if (len === 5 && !cards.some(c => c.rank === '2') && isConsecutive(ranksIndices))
                return {
                    type: 'straight',
                    rank: ranksIndices[len - 1]
                };

            return null;
        }

        function createDeck() {
            let deck = [];
            for (let suit of suits) {
                for (let rank of ranks) {
                    deck.push({
                        rank,
                        suit: suit.symbol,
                        suitName: suit.name
                    });
                }
            }
            return deck;
        }

        function shuffle(deck) {
            for (let i = deck.length - 1; i > 0; i--) {
                let j = Math.floor(Math.random() * (i + 1));
                [deck[i], deck[j]] = [deck[j], deck[i]];
            }
        }

        function createCardElement(card, faceUp = true) {
            let cardDiv = document.createElement('div');
            cardDiv.classList.add('card');

            if (!faceUp) {
                cardDiv.classList.add('back');
                cardDiv.title = "Bài úp của Bot";
            } else {
                cardDiv.innerHTML = `<div class="rank">${card.rank}</div><div class="suit ${card.suitName}">${card.suit}</div>`;

                cardDiv.onclick = () => {
                    if (isPlayerTurn) {
                        toggleSelectCard(cardDiv, card);
                    }
                };
            }

            return cardDiv;
        }

        function toggleSelectCard(cardDiv, card) {
            const index = selectedCards.findIndex(c => c.rank === card.rank && c.suit === card.suit);
            if (index !== -1) {
                selectedCards.splice(index, 1);
                cardDiv.classList.remove('selected');
            } else {
                selectedCards.push(card);
                cardDiv.classList.add('selected');
            }
        }

        function playSelectedCards() {
            if (!isPlayerTurn || selectedCards.length === 0) return;

            // Nếu là lượt đầu tiên và người chơi có 3♠ thì bắt buộc phải đánh đúng lá đó
            if (isFirstTurn) {
                const has3S = playerHand.some(c => c.rank === '3' && c.suit === '♠');
                const played3S = selectedCards.some(c => c.rank === '3' && c.suit === '♠');

                if (has3S && !played3S) {
                    alert("Bạn có 3♠, bạn phải đánh lá này đầu tiên!");
                    return;
                }
            }

            // Lưu bài đánh trước
            lastPlayedCards = [...selectedCards];

            selectedCards.forEach(card => {
                removeCardFromHand(playerHand, card);
            });

            showMultiplePlayedCards(selectedCards, 'player-card');
            selectedCards = [];
            renderHands();

            if (playerHand.length === 0) {
                alert('Bạn đã đánh hết bài! Chia bài mới...');
                dealCards();
                return;
            }

            isPlayerTurn = false;
            isFirstTurn = false;
            document.getElementById('game-status').textContent = 'Đến lượt Bot đánh bài...';

            setTimeout(botPlayCard, 1500);
        }



        function dealCards() {
            const deck = createDeck();
            shuffle(deck);

            playerHand = deck.slice(0, 13);
            botHand = deck.slice(13, 26);

            playerHand.sort((a, b) => getRankIndex(a.rank) - getRankIndex(b.rank));
            botHand.sort((a, b) => getRankIndex(a.rank) - getRankIndex(b.rank));

            const playerHas3S = playerHand.some(c => c.rank === '3' && c.suit === '♠');
            const botHas3S = botHand.some(c => c.rank === '3' && c.suit === '♠');
            isFirstTurn = true;


            isPlayerTurn = playerHas3S;
            document.getElementById('game-status').textContent =
                isPlayerTurn ? 'Bạn có 3♠, bạn đi trước!' : 'Bot có 3♠, bot đi trước!';

            renderHands();
            clearPlayedCards();

            if (!isPlayerTurn) {
                setTimeout(botPlayCard, 1000);
            }

            document.getElementById('deal-btn').disabled = true;
        }

        function renderHands() {
            const playerHandDiv = document.getElementById('player-hand');
            playerHandDiv.innerHTML = '';
            playerHand.forEach(card => {
                playerHandDiv.appendChild(createCardElement(card, true));
            });

            const botHandDiv = document.getElementById('bot-hand');
            botHandDiv.innerHTML = '';
            botHand.forEach(() => {
                botHandDiv.appendChild(createCardElement(null, false));
            });
        }

        function clearPlayedCards() {
            document.getElementById('played-cards').innerHTML = '';
        }

        function playerPlayCard(cardDiv, card) {
            if (!isPlayerTurn) return;
            removeCardFromHand(playerHand, card);
            showPlayedCard(card, 'player-card');
            renderHands();

            if (playerHand.length === 0) {
                alert('Bạn đã đánh hết bài! Chia bài mới...');
                dealCards();
                return;
            }

            isPlayerTurn = false;
            document.getElementById('game-status').textContent = 'Đến lượt Bot đánh bài...';

            setTimeout(botPlayCard, 1500);
        }

        function botPlayCard() {

            console.log("Bot hand:", botHand);
            console.log("isFirstTurn:", isFirstTurn);

            if (botHand.length === 0) {
                alert('Bot đã đánh hết bài! Chia bài mới...');
                dealCards();
                return;
            }

            let botPlay = null;

            if (isFirstTurn) {
                const card3S = botHand.find(c => c.rank === '3' && c.suit === '♠');
                if (card3S) {
                    botPlay = [card3S];
                }
            } else if (!lastPlayedCards) {
                botPlay = [botHand[0]];
            } else {
                const target = classifyCards(lastPlayedCards);
                let candidates = findMatchingCards(botHand, target);

                // Nếu người chơi đánh đôi 2 và Bot không có bài chặn thông thường
                if (target.type === 'pair' && lastPlayedCards[0].rank === '2' && candidates.length === 0) {
                    const counter = counterDoubleTwo(botHand, lastPlayedCards);
                    if (counter) {
                        candidates = [counter];
                    }
                }

                if (candidates.length > 0) {
                    botPlay = candidates[0];
                }
            }

            if (botPlay) {
                botPlay.forEach(card => removeCardFromHand(botHand, card));
                showMultiplePlayedCards(botPlay, 'bot-card');
                lastPlayedCards = [...botPlay];
            } else {
                document.getElementById('game-status').textContent = 'Bot bỏ lượt. Lượt bạn đánh!';
                isPlayerTurn = true;
                return;
            }

            renderHands();

            if (botHand.length === 0) {
                alert('Bot đã đánh hết bài! Chia bài mới...');
                dealCards();
                return;
            }

            isFirstTurn = false;
            document.getElementById('game-status').textContent = 'Lượt bạn đánh!';
            isPlayerTurn = true;
        }



        function showPlayedCard(card, className) {
            if (!card || !card.rank || !card.suit) {
                console.error('Lá bài không hợp lệ:', card);
                return;
            }

            const playedCardsDiv = document.getElementById('played-cards');
            playedCardsDiv.innerHTML = '';

            let cardDiv = createCardElement(card, true);
            cardDiv.style.width = '100px';
            cardDiv.style.height = '150px';
            cardDiv.style.transform = 'scale(1.1)';
            cardDiv.style.boxShadow = '0 8px 20px rgba(0,0,0,0.7)';
            cardDiv.style.cursor = 'default';
            cardDiv.style.transition = 'transform 0.3s ease';

            if (className === 'player-card') {
                cardDiv.style.border = '3px solid #4caf50';
            } else if (className === 'bot-card') {
                cardDiv.style.border = '3px solid #f44336';
            }

            playedCardsDiv.appendChild(cardDiv);
        }

        function showMultiplePlayedCards(cards, className) {
            const playedCardsDiv = document.getElementById('played-cards');
            playedCardsDiv.innerHTML = '';

            cards.forEach(card => {
                let cardDiv = createCardElement(card, true);
                cardDiv.style.width = '80px';
                cardDiv.style.height = '120px';
                cardDiv.style.margin = '0 5px';
                cardDiv.style.transform = 'scale(1.05)';
                cardDiv.style.boxShadow = '0 6px 15px rgba(0,0,0,0.5)';
                cardDiv.style.cursor = 'default';

                if (className === 'player-card') {
                    cardDiv.style.border = '3px solid #4caf50';
                } else if (className === 'bot-card') {
                    cardDiv.style.border = '3px solid #f44336';
                }

                playedCardsDiv.appendChild(cardDiv);
            });
        }

        function findMatchingCards(hand, target) {
            if (!target) return [];

            let results = [];
            const grouped = groupByRank(hand);

            switch (target.type) {
                case 'single':
                    results = hand.filter(c => getRankIndex(c.rank) > target.rank).map(c => [c]);
                    break;

                case 'pair':
                    for (let rank in grouped) {
                        if (grouped[rank].length >= 2 && getRankIndex(rank) > target.rank) {
                            results.push(grouped[rank].slice(0, 2));
                        }
                    }

                    // Nếu người chơi đánh đôi 2, cho phép bot chặn bằng tứ quý
                    if (target.rank === getRankIndex('2')) {
                        for (let rank in grouped) {
                            if (grouped[rank].length === 4) {
                                results.push(grouped[rank]);
                            }
                        }
                    }
                    break;


                case 'bomb':
                    for (let rank in grouped) {
                        if (grouped[rank].length === 4 && getRankIndex(rank) > target.rank) {
                            results.push(grouped[rank]);
                        }
                    }
                    break;

                case 'straight':
                    const straights = findStraights(hand, 5);
                    for (let s of straights) {
                        const maxRank = getRankIndex(s[s.length - 1].rank);
                        if (maxRank > target.rank) results.push(s);
                    }
                    break;
                case 'double-sequence':
                    if (target.type === 'pair' && target.rank === getRankIndex('2')) {
                        const doubleSequences = findDoubleSequences(hand, 3);
                        results.push(...doubleSequences);
                    }
            }

            return results;
        }

        function findDoubleSequences(cards, sequenceLength = 3) {
            const grouped = groupByRank(cards);
            let validRanks = [];

            for (let rank of ranks) {
                if (grouped[rank] && grouped[rank].length >= 2 && rank !== '2') {
                    validRanks.push(rank);
                }
            }

            const sequences = [];
            for (let i = 0; i <= validRanks.length - sequenceLength; i++) {
                const segment = validRanks.slice(i, i + sequenceLength);
                const indices = segment.map(r => getRankIndex(r));
                if (isConsecutive(indices)) {
                    const combo = [];
                    segment.forEach(rank => {
                        combo.push(...grouped[rank].slice(0, 2));
                    });
                    sequences.push(combo);
                }
            }

            return sequences;
        }


        function groupByRank(cards) {
            return cards.reduce((acc, card) => {
                if (!acc[card.rank]) acc[card.rank] = [];
                acc[card.rank].push(card);
                return acc;
            }, {});
        }

        function getCardValue(card) {
            const order = ['3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A', '2'];
            return order.indexOf(card.rank);
        }

        function findStraights(cards, length = 5) {
            let results = [];
            const uniqueRanks = [...new Set(cards.map(c => c.rank))].sort((a, b) => getRankIndex(a) - getRankIndex(b));
            const rankToCard = {};
            for (let card of cards) {
                rankToCard[card.rank] = card; // giữ lại 1 lá mỗi rank
            }

            for (let i = 0; i <= uniqueRanks.length - length; i++) {
                const slice = uniqueRanks.slice(i, i + length);
                const indices = slice.map(r => getRankIndex(r));
                if (isConsecutive(indices) && !slice.includes('2')) {
                    results.push(slice.map(r => rankToCard[r]));
                }
            }

            return results;
        }


        function removeCardFromHand(hand, card) {
            const index = hand.findIndex(c => c.rank === card.rank && c.suit === card.suit);
            if (index !== -1) {
                hand.splice(index, 1);
            }
        }
        //hàm sử lý tứ quý
        function findFourOfAKind(hand) {
            const grouped = groupByRank(hand);
            for (let rank in grouped) {
                if (grouped[rank].length === 4) {
                    return grouped[rank]; // Trả về mảng 4 lá giống nhau
                }
            }
            return null;
        }
        //hàm tìm 3 đôi thông
        function findThreeConsecutivePairs(hand) {
            const grouped = groupByRank(hand);
            const pairs = [];

            // Lấy các cặp
            for (let rank in grouped) {
                if (grouped[rank].length >= 2) {
                    pairs.push({
                        rank: getCardValue(grouped[rank][0]),
                        cards: grouped[rank].slice(0, 2)
                    });
                }
            }

            // Sắp xếp theo thứ tự tăng dần
            pairs.sort((a, b) => a.rank - b.rank);

            for (let i = 0; i < pairs.length - 2; i++) {
                if (pairs[i].rank + 1 === pairs[i + 1].rank &&
                    pairs[i + 1].rank + 1 === pairs[i + 2].rank) {
                    return [...pairs[i].cards, ...pairs[i + 1].cards, ...pairs[i + 2].cards];
                }
            }

            return null;
        }
        //hàm tổng quát để tìm chặn đôi 2
        function counterDoubleTwo(hand, playedCards) {
            if (playedCards.length !== 2 || playedCards[0].rank !== '2') return null;

            const fourKind = findFourOfAKind(hand);
            if (fourKind) return fourKind;

            const threePairs = findThreeConsecutivePairs(hand);
            if (threePairs) return threePairs;

            return null;
        }
    </script>
</body>

</html>