import './bootstrap.js';
import DeckView from './scripts/Deck/DeckView.js';
import { formatFileName} from './scripts/functions.js';

document.addEventListener('DOMContentLoaded', () => {
    const deckView = new DeckView();
    deckView.crud();
    formatFileName();
});
