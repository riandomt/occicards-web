document.addEventListener('DOMContentLoaded', () => {
    const deckName = document.getElementById('deck_name');
    const deckDescription = document.getElementById('deck_description');
    const deckCards = document.getElementById('deck_cards');

    function updateDeckCards() {
        deckCards.value = JSON.stringify({
            name: deckName.value,
            description: deckDescription.value,
            deck: {}
        }, null, 2); // null,2 => joli format
    }

    // Initialiser dès le chargement
    updateDeckCards();

    // Mettre à jour en temps réel
    deckName.addEventListener('input', updateDeckCards);
    deckDescription.addEventListener('input', updateDeckCards);
});
