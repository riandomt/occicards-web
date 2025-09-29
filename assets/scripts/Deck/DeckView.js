import Deck from './Deck.js';
import Card from './Card.js';

export default class DeckView {
    constructor() {
        // Instance unique de Deck
        this._deck = new Deck('', '');
        this.setName();
        this.setDescription();
        this.setCards();
        this.setTable();
    }

    // --- accès internes ---
    getDeck() { return this._deck; }

    getName() { return this._name; }
    setName() { this._name = document.getElementById('deck_name'); }

    getDescription() { return this._description; }
    setDescription() { this._description = document.getElementById('deck_description'); }

    getCards() { return this._cards; }
    setCards() { this._cards = document.getElementById('deck_cards'); }

    getTable() { return this._table; }
    setTable() { this._table = document.querySelector("#cardTable tbody"); }

    // --- met à jour le JSON ---
    syncJson() {
        this.getCards().value = this.getDeck().getJson();
        console.log("Deck JSON mis à jour :", this.getCards().value);
    }

    // --- synchronisation name + description ---
    getNameAndDescription() {
        this.getName().addEventListener('input', () => {
            this.getDeck().setName(this.getName().value);
            this.syncJson();
        });

        this.getDescription().addEventListener('input', () => {
            this.getDeck().setDescription(this.getDescription().value);
            this.syncJson();
        });
    }

    // --- CREATE ---
    create() {
        const btnCreate = document.getElementById("btnCreateCard");

        btnCreate.addEventListener("click", () => {
            const question = document.getElementById("card_question");
            const answer = document.getElementById("card_answer");
            const card = new Card(question.value, answer.value);

            this.getDeck().addCard(card);
            this.syncJson();

            const row = document.createElement("tr");
            row.id = `card-${this.getDeck().getId()}`;
            row.innerHTML = `
                <td scope="row">${this.getDeck().getId()}</td>
                <td>${question.value}</td>
                <td>${answer.value}</td>
                <td>
                    <button type="button" class="btn btn-primary" 
                            data-bs-toggle="modal" 
                            data-bs-target="#updateModal" 
                            data-id="${this.getDeck().getId()}">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="button" class="btn btn-danger">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </td>`;

            this.getTable().appendChild(row);

            question.value = '';
            answer.value = '';
        });
    }

    // --- EDIT (pré-remplissage de la modale update) ---
    edit() {
        const updateModal = document.getElementById('updateModal');

        updateModal.addEventListener('show.bs.modal', (event) => {
            const button = event.relatedTarget;
            const cardId = button.getAttribute('data-id');

            updateModal.setAttribute('data-edit-id', cardId);

            const row = document.querySelector(`#card-${cardId}`);

            document.querySelector('#updateModal #card_question').value = row.children[1].innerText;
            document.querySelector('#updateModal #card_answer').value = row.children[2].innerText;

            console.log("Pré-remplissage des champs pour édition :", {
                id: cardId,
                question: row.children[1].innerText,
                answer: row.children[2].innerText
            });
        });
    }

    // --- UPDATE ---
    update() {
        const btnUpdate = document.getElementById("btnUpdateCard");
        const updateModal = document.getElementById('updateModal');

        btnUpdate.addEventListener("click", () => {
            const cardId = updateModal.getAttribute('data-edit-id');
            const row = document.querySelector(`#card-${cardId}`);

            const question = document.querySelector('#updateModal #card_question');
            const answer = document.querySelector('#updateModal #card_answer');

            // Mise à jour du tableau
            row.children[1].innerText = question.value;
            row.children[2].innerText = answer.value;

            // Mise à jour dans le Deck
            if (typeof this.getDeck().updateCard === "function") {
                const card = new Card(question.value, answer.value);
                card.setId(parseInt(cardId.replace("card-", "")));
                this.getDeck().updateCard(card);
                this.syncJson();
            }

            // Reset
            question.value = '';
            answer.value = '';

            // Fermer la modale
            const modal = bootstrap.Modal.getInstance(updateModal);
            modal.hide();
        });
    }

    // --- DELETE ---
    delete() {
        this.getTable().addEventListener("click", (event) => {
            if (event.target.closest(".btn-danger")) {
                const row = event.target.closest("tr");
                const cardId = row.id; // ex: "card-1"

                if (confirm("Voulez-vous supprimer la carte ?")) {
                    row.remove();

                    if (typeof this.getDeck().deleteCard === "function") {
                        this.getDeck().deleteCard(cardId.replace("card-", ""));
                        this.syncJson();
                    }
                }
            }
        });
    }

    // --- CRUD ---
    crud() {
        this.getNameAndDescription();
        this.create();
        this.edit();
        this.update();
        this.delete();
    }
}
