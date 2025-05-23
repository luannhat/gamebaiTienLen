let playerHand = [];
let selectedCards = [];

function startGame() {
  fetch('game.php?action=deal')
    .then(res => res.json())
    .then(data => {
      playerHand = data.player;
      selectedCards = [];
      renderHands();
    });
}

function renderHands() {
  const playerDiv = document.getElementById('player-hand');
  playerDiv.innerHTML = '';
  playerHand.forEach((card, index) => {
    const cardDiv = document.createElement('div');
    cardDiv.className = 'card';
    cardDiv.innerText = `${card.value}${card.suit}`;
    cardDiv.onclick = () => toggleSelect(index, cardDiv);
    if (selectedCards.includes(index)) {
      cardDiv.classList.add('selected');
    }
    playerDiv.appendChild(cardDiv);
  });
}

function toggleSelect(index) {
  if (selectedCards.includes(index)) {
    selectedCards = selectedCards.filter(i => i !== index);
  } else {
    selectedCards.push(index);
  }
  renderHands();
}

function playSelected() {
  if (selectedCards.length === 0) {
    alert("Chọn bài để đánh");
    return;
  }

  const selected = selectedCards.map(i => playerHand[i]);
  fetch('game.php?action=play', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ cards: selected })
  })
  .then(res => res.json())
  .then(data => {
    document.getElementById('table-cards').innerText = "Người chơi đánh: " + data.played.map(c => c.value + c.suit).join(", ");
    playerHand = playerHand.filter((_, i) => !selectedCards.includes(i));
    selectedCards = [];
    renderHands();
  });
}
