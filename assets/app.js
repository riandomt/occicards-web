import './bootstrap.js';
import { formatFileName, deckManager } from './scripts/functions.js';

document.addEventListener('DOMContentLoaded', () => {
    formatFileName();
    deckManager();
});
