export const LKSConfig = {
  API_BASE_URL: import.meta.env.PROD
    ? "/server"
    : "http://localhost:8000",

  GAME_TITLE: "Pattern Hacker",
  VERSION: "1.0.0"
};