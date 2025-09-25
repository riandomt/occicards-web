import Deck from './Deck.js';

export default class DeckManager {
    constructor() {
        this.setDecks({});
    }

    getDecks() { return this.decks; }
    setDecks(newDecks) { this.decks = newDecks; }

    createDeck(level, name, description) {
        const id = Object.keys(this.getDecks()).length + 1;
        const newDeck = new Deck(level, name, description);

        let decks = this.getDecks();
        decks[id] = newDeck;
        this.setDecks(decks);

        return newDeck;
    }

    getDeck(id) {
        return this.getDecks()[id] || null;
    }

    listDecks() {
        return Object.values(this.getDecks());
    }
}
