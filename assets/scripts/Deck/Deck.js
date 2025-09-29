class Deck {
  constructor(name, description, level = 0, cards = {}) {
    this.setName(name);
    this.setDescription(description);
    this.setLevel(level);
    this.setCards(cards);
    this.setId(Object.keys(cards).length);
  }

  // --- name ---
  getName() { return this._name; }
  setName(name) { this._name = name; }

  // --- description ---
  getDescription() { return this._description; }
  setDescription(description) { this._description = description; }

  // --- level ---
  getLevel() { return this._level; }
  setLevel(level) { this._level = level; }

  // --- cards ---
  getCards() { return this._cards; }
  setCards(cards) {
    this._cards = cards;
  }

  // --- id ---
  getId() { return this._id; }
  setId(id) { this._id = id; }

  generateId() {
    this.setId(this.getId() + 1);
  }

  // --- méthodes métier ---
  addCard(card) {
    this.generateId();
    const cards = this.getCards();

    const id = this.getId();
    const question = card.getQuestion();
    const answer = card.getAnswer();
    console.log(id, question, answer);
    cards[id] = { [question]: answer };
    this.setCards(cards);

    return id;
  }

  updateCard(card) {
    const cards = this.getCards();

    cards[card.getId()] = {
      question: card.getQuestion(),
      answer: card.getAnswer()
    };
    
    this.setCards(cards);
  }

  deleteCard(id) {
    const cards = this.getCards();
    delete cards[id];
    this.setCards(cards);
  }

  // --- export ---
  getObject() {
    return {
      name: this.getName(),
      description: this.getDescription(),
      level: this.getLevel(),
      cards: this.getCards()
    };
  }

  getJson() {
    return JSON.stringify(this.getObject(), null, 2);
  }
}

export default Deck;
