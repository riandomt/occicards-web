class Card {
  constructor(id = 0, question, answer) {
    this.setId(id);
    this.setQuestion(question);
    this.setAnswer(answer);
  }

  // --- id ---
  getId() {
    return this._id;
  }
  setId(id) {
    this._id = id;
  }

  // --- question ---
  getQuestion() {
    return this._question;
  }
  setQuestion(question) {
    this._question = question;
  }

  // --- answer ---
  getAnswer() {
    return this._answer;
  }
  setAnswer(answer) {
    this._answer = answer;
  }
}

export default Card;
