function playCard(index) {
    fetch('game.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({index: index})
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById("message").innerText = data.message;
        if (data.reload) {
            setTimeout(() => window.location.reload(), 1000);
        }
    });
}
