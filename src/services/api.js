import { LKSConfig } from "../config.js";
import { useAuthStore } from "../store/useAuthStore.js";

/**
 * API Service — Singleton
 * 
 * Fixes:
 * - CSRF token attached to all POST requests automatically
 * - AbortController signal support on fetch calls
 * - New endpoints: checkSession, endGame, resetGame
 */
class API {
  constructor() {
    this.baseURL = LKSConfig.API_BASE_URL;
  }

  // =========================
  // CORE REQUEST HANDLER
  // =========================
  async request(endpoint, method = "GET", data = null, signal = null) {
    const headers = {
      "Content-Type": "application/json"
    };

    // Attach CSRF token to all POST requests
    if (method === "POST") {
      const csrfToken = useAuthStore.getState().csrfToken;
      if (csrfToken) {
        headers["X-CSRF-Token"] = csrfToken;
      }
    }

    const options = {
      method,
      headers,
      credentials: "include" // required for PHP session cookies
    };

    if (data) {
      options.body = JSON.stringify(data);
    }

    if (signal) {
      options.signal = signal;
    }

    const response = await fetch(
      `${this.baseURL}${endpoint}`,
      options
    );

    const result = await response.json();

    if (!response.ok) {
      throw new Error(result.message || result.error || "API Error");
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

  /** Validates if the backend session is still alive */
  checkSession() {
    return this.request("/auth/me", "GET");
  }

  // =========================
  // GAME API
  // =========================
  generateQuestion(signal = null) {
    return this.request("/generate-question", "POST", null, signal);
  }

  submitAnswer(answer) {
    return this.request("/submit-answer", "POST", { answer });
  }

  /** Ends the game (timer expiry) — saves score on backend */
  endGame() {
    return this.request("/end-game", "POST");
  }

  /** Force-resets the game session on the backend */
  resetGame() {
    return this.request("/reset-game", "POST");
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

// Singleton instance
export const api = new API();