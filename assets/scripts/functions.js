import Deck from './Deck/Deck.js';
import Card from './Deck/Card.js';

export function formatFileName() {
    document.addEventListener('input', (e) => {
        if (!e.target.matches('.name')) return;

        e.target.value = e.target.value
            .toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^a-z0-9-]/g, '')
            .replace(/-+/g, '-')
            .replace(/^-+/, '')
            .replace(/^[0-9]+/, '');
    });
}

export function deckManager() {
    const name = document.getElementById('deck_name');
    const description = document.getElementById('deck_description');
    const cards = document.getElementById('deck_cards')

    const deck = new Deck(name.value, description.value);
    const btnCreate = document.getElementById("btnCreateCard");

  btnCreate.addEventListener("click", () => {
    const question = document.getElementById("card_question").value.trim();
    const answer = document.getElementById("card_answer").value.trim();
    const card = new Card(question.value, answer.value);
    const id = deck.addCard(card);
    cards.value = deck.getJson();
    console.log('Ajout de la carte : ' + deck.getJson());
  });

    
    
    deck.updateCard(new Card(1, 'Question 2', 'Reponse 2'));
    console.log('Modification de la carte : ' + deck.getJson());

    deck.deleteCard(id);
    console.log('Supression de la carte : ' + deck.getJson());

    deck.updateCard(card);
}