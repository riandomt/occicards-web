import Deck from './Deck.js';
import Card from './Card.js';

export default class DeckView {
    constructor() {
        this.setName();
        this.setDescription();
        this.setCards();
        this.setTable();
    }

    getName() { return this._name; }
    setName() { this._name = document.getElementById('deck_name'); }

    getDescription() { return this._description; }
    setDescription() { this._description = document.getElementById('deck_description') }

    getCards() { return this._cards; }
    setCards(cards) { this._cards = document.getElementById('deck_cards'); }

    getTable() { return this._table }
    setTable() { this._table = document.querySelector("#cardTable tbody"); }
    create() {
        const deck = new Deck(this.getName().value, this.getDescription().value);
        const btnCreate = document.getElementById("btnCreateCard");

        btnCreate.addEventListener("click", () => {
            const question = document.getElementById("card_question");
            const answer = document.getElementById("card_answer");
            const card = new Card(question.value, answer.value);

            deck.addCard(card);
            this.getCards().value = deck.getJson();

            console.log('Ajout de la carte : ' + deck.getJson());

            const row = document.createElement("tr");
            row.innerHTML = `
                <td scope="row">${deck.getId()}</td>
                <td>${question.value}</td>
                <td>${answer.value}</td>
    `;
            this.getTable().appendChild(row);
            question.value = '';
            answer.value = '';
        });

    }

    update() {

    }

    delete() {

    }
}
