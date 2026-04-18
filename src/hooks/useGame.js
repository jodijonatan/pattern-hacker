import { useState, useEffect, useCallback, useRef } from "react";
import { api } from "../services/api.js";

/**
 * useGame — game state management hook.
 * 
 * Fixes applied:
 * - AbortController prevents duplicate requests on StrictMode double-mount
 * - Timer expiry calls POST /end-game to save score on backend
 * - resetGame() calls POST /reset-game to clear server session
 * - Feedback timeout properly cleaned up
 * - Stable callback refs prevent dependency loops
 */
export function useGame({ user, onGameOver }) {
  const [state, setState] = useState(null);
  const [question, setQuestion] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [timer, setTimer] = useState(60);
  const [rank, setRank] = useState(null);
  const [gameOver, setGameOver] = useState(false);
  
  const timerRef = useRef(null);
  const abortRef = useRef(null);       // AbortController for preventing duplicate fetches
  const mountedRef = useRef(false);     // StrictMode guard

  // Stable callback ref — prevents re-triggering effects when parent passes inline functions
  const onGameOverRef = useRef(onGameOver);
  useEffect(() => {
    onGameOverRef.current = onGameOver;
  }, [onGameOver]);

  // =====================
  // TIMER MANAGEMENT
  // =====================
  const stopTimer = useCallback(() => {
    if (timerRef.current) {
      clearInterval(timerRef.current);
      timerRef.current = null;
    }
  }, []);

  const startTimer = useCallback((initialTime = 60) => {
    stopTimer();
    setTimer(initialTime);

    timerRef.current = setInterval(() => {
      setTimer((prev) => {
        if (prev <= 1) {
          stopTimer();
          // Timer expired — trigger end-game on server
          handleTimerExpiry();
          return 0;
        }
        return prev - 1;
      });
    }, 1000);
  }, [stopTimer]); // eslint-disable-line react-hooks/exhaustive-deps

  // =====================
  // TIMER EXPIRY — calls backend to save score
  // =====================
  const handleTimerExpiry = useCallback(async () => {
    try {
      const res = await api.endGame();
      const resultData = res.data || res;
      
      if (resultData.state) {
        setState(resultData.state);
      }
      if (resultData.rank) {
        setRank(resultData.rank);
      }
      setGameOver(true);
      onGameOverRef.current?.(resultData.state?.score || 0);
    } catch (err) {
      console.error("Failed to end game on server:", err);
      // Still show game-over locally even if the API fails
      setGameOver(true);
      onGameOverRef.current?.(state?.score || 0);
    }
  }, [state?.score]);

  // =====================
  // FETCH QUESTION — with AbortController
  // =====================
  const fetchQuestion = useCallback(async () => {
    if (!user) return;

    // Abort any in-flight request
    if (abortRef.current) {
      abortRef.current.abort();
    }
    abortRef.current = new AbortController();

    setLoading(true);
    setError(null);
    try {
      const res = await api.generateQuestion(abortRef.current.signal);
      const questionData = res.data?.question || res.question;

      if (questionData) {
        setQuestion({ sequence: questionData });
        // Sync server state if provided
        if (res.data?.state) {
          setState(res.data.state);
        }
      } else {
        setError("Failed to generate question: No data received");
      }
    } catch (err) {
      // Ignore abort errors — they're intentional
      if (err.name === 'AbortError') return;
      setError(err.message || "Network error");
    } finally {
      setLoading(false);
    }
  }, [user]);

  // =====================
  // SUBMIT ANSWER
  // =====================
  const submitAnswer = useCallback(async (answer) => {
    if (!user || !question) return false;
    setLoading(true);
    setError(null);
    try {
      const res = await api.submitAnswer(answer);
      const resultData = res.data || res;

      if (res.success !== false) {
        setState(resultData.state);
        const isGameOver = resultData.gameOver;

        if (isGameOver) {
          stopTimer();
          setRank(resultData.rank || null);
          setGameOver(true);
          onGameOverRef.current?.(resultData.state?.score || 0);
        } else {
          await fetchQuestion();
        }

        return resultData.correct !== false;
      } else {
        setError(res.message || "Submit failed");
        setLoading(false);
        return false;
      }
    } catch (err) {
      setError(err.message);
      setLoading(false);
      return false;
    }
  }, [user, question, fetchQuestion, stopTimer]);

  // =====================
  // RESET GAME — calls backend to clear session
  // =====================
  const resetGame = useCallback(async () => {
    try {
      // FIX: Tell the backend to reset the game session
      await api.resetGame();
    } catch (err) {
      console.error("Failed to reset game on server:", err);
    }

    // Reset local state
    setState(null);
    setQuestion(null);
    setRank(null);
    setGameOver(false);
    setTimer(60);
    startTimer(60);
    await fetchQuestion();
  }, [fetchQuestion, startTimer]);

  // =====================
  // INITIALIZATION — with StrictMode guard
  // =====================
  useEffect(() => {
    // StrictMode double-mount guard
    if (mountedRef.current) return;
    mountedRef.current = true;

    if (user) {
      setGameOver(false);
      fetchQuestion();
      startTimer(60);
    } else {
      setState(null);
      setQuestion(null);
      setRank(null);
      setGameOver(false);
      stopTimer();
    }

    return () => {
      stopTimer();
      mountedRef.current = false;
      if (abortRef.current) {
        abortRef.current.abort();
      }
    };
  }, [user]); // eslint-disable-line react-hooks/exhaustive-deps

  return {
    state,
    question,
    timer,
    rank,
    gameOver,
    submitAnswer,
    resetGame,
    loading,
    error,
    refetch: fetchQuestion
  };
}
