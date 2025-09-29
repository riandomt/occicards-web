class Card {
  constructor(question, answer) {
    this.setQuestion(question);
    this.setAnswer(answer);
  }

  getQuestion() { return this._question; }
  setQuestion(question) { this._question = question.trim(); }

  getAnswer() { return this._answer; }
  setAnswer(answer) { this._answer = answer.trim(); }
}

export default Card;
