import { LKSConfig } from "../config.js";

class API {
  constructor() {
    this.baseURL = LKSConfig.API_BASE_URL;
  }

  // =========================
  // CORE REQUEST HANDLER
  // =========================
  async request(endpoint, method = "GET", data = null) {
    const options = {
      method,
      headers: {
        "Content-Type": "application/json"
      },
      credentials: "include" // penting untuk session PHP
    };

    if (data) {
      options.body = JSON.stringify(data);
    }

    const response = await fetch(
      `${this.baseURL}${endpoint}`,
      options
    );

    const result = await response.json();

    if (!response.ok) {
      throw new Error(result.error || "API Error");
    }

    return result;
  }

  // =========================
  // AUTH API
  // =========================
  login(data) {
    return this.request("/auth/login", "POST", data);
  }

  register(data) {
    return this.request("/auth/register", "POST", data);
  }

  logout() {
    return this.request("/auth/logout", "POST");
  }

  // =========================
  // GAME API
  // =========================
  generateQuestion() {
    return this.request("/generate-question", "POST");
  }

  submitAnswer(answer) {
    return this.request("/submit-answer", "POST", { answer });
  }

  getScore() {
    return this.request("/get-score", "GET");
  }

  // =========================
  // LEADERBOARD API
  // =========================
  getLeaderboard(difficulty = null) {
    const query = difficulty ? `?difficulty=${difficulty}` : "";
    return this.request(`/leaderboard${query}`, "GET");
  }
}

// singleton instance
export const api = new API();