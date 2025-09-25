import Card from './Card.js';

export default class Deck {
    constructor(level = 0, name = '', description = '') {
        this.setLevel(level);
        this.setName(name);
        this.setDescription(description);
        this.setCards({});
    }

    // --- Propriétés du deck ---
    getLevel() { return this.level; }
    setLevel(newLevel) { this.level = newLevel; }

    getName() { return this.name; }
    setName(newName) { this.name = newName; }

    getDescription() { return this.description; }
    setDescription(newDescription) { this.description = newDescription; }

    getCards() { return this.cards; }
    setCards(newCards) { this.cards = newCards; }

    // --- Gestion des cartes ---
    generateId() {
        const keys = Object.keys(this.getCards()).map(Number);
        return keys.length ? Math.max(...keys) + 1 : 1;
    }

    addCard(question, answer) {
        const id = this.generateId();
        const newCard = new Card(id, question, answer);

        let cards = this.getCards();
        cards[id] = newCard;
        this.setCards(cards);

        return id;
    }

    getCard(id) {
        return this.getCards()[id] || null;
    }

    updateCard(id, question, answer) {
        const card = this.getCard(id);
        if (card) {
            card.setQuestion(question);
            card.setAnswer(answer);
            return true;
        }
        return false;
    }

    deleteCard(id) {
        let cards = this.getCards();
        if (cards[id]) {
            delete cards[id];
            this.setCards(cards);
            return true;
        }
        return false;
    }

    listCards() {
        return Object.values(this.getCards());
    }
}
