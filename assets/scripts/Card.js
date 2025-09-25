export default class Card {
    constructor(id, question = '', answer = '') {
        this.setId(id);
        this.setQuestion(question);
        this.setAnswer(answer);
    }

    getId() { return this.id; }
    setId(newId) { this.id = newId; }

    getQuestion() { return this.question; }
    setQuestion(newQuestion) { this.question = newQuestion; }

    getAnswer() { return this.answer; }
    setAnswer(newAnswer) { this.answer = newAnswer; }
}
