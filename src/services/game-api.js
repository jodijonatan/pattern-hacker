const BASE_URL = "http://localhost/server";

export const GameAPI = {
  async generateQuestion() {
    const res = await fetch(`${BASE_URL}/generate-question`, {
      method: "POST",
      credentials: "include",
    });
    return res.json();
  },

  async submitAnswer(answer) {
    const res = await fetch(`${BASE_URL}/submit-answer`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      credentials: "include",
      body: JSON.stringify({ answer }),
    });

    return res.json();
  },

  async getScore() {
    const res = await fetch(`${BASE_URL}/get-score`, {
      credentials: "include",
    });
    return res.json();
  },
};