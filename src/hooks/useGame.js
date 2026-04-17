import { useState, useEffect, useCallback, useRef } from "react";
import { api } from "../services/api.js";

export function useGame({ user, onGameOver }) {
  const [state, setState] = useState(null);
  const [question, setQuestion] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [timer, setTimer] = useState(60); 
  const [rank, setRank] = useState(null);
  const timerRef = useRef(null);
  
  // Ref for callbacks to prevent dependency loops when parent passes inline functions
  const onGameOverRef = useRef(onGameOver);
  useEffect(() => {
    onGameOverRef.current = onGameOver;
  }, [onGameOver]);

  const stopTimer = useCallback(() => {
    if (timerRef.current) {
      clearInterval(timerRef.current);
      timerRef.current = null;
    }
  }, []);

  const fetchQuestion = useCallback(async () => {
    if (!user) return;
    setLoading(true);
    setError(null);
    try {
      const res = await api.generateQuestion();
      const questionData = res.data?.question || res.question;
      
      if (questionData) {
        setQuestion({ sequence: questionData });
      } else {
        setError("Failed to generate question: No data received");
      }
    } catch (err) {
      setError(err.message || "Network error");
    } finally {
      setLoading(false);
    }
  }, [user]);

  const startTimer = useCallback((initialTime = null) => {
    stopTimer();
    if (initialTime !== null) {
      setTimer(initialTime);
    }
    
    timerRef.current = setInterval(() => {
      setTimer((prev) => {
        if (prev <= 1) {
          stopTimer();
          onGameOverRef.current?.(state?.score || 0);
          return 0;
        }
        return prev - 1;
      });
    }, 1000);
  }, [state?.score, stopTimer]);

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

  const resetGame = useCallback(async () => {
    setState(null);
    setQuestion(null);
    setRank(null);
    setTimer(60);
    startTimer(60);
    await fetchQuestion();
  }, [fetchQuestion, startTimer]);

  useEffect(() => {
    if (user) {
      fetchQuestion();
      startTimer(60);
    } else {
      setState(null);
      setQuestion(null);
      setRank(null);
      stopTimer();
    }
    return () => stopTimer();
  }, [user]);

  return {
    state,
    question,
    timer,
    rank,
    submitAnswer,
    resetGame,
    loading,
    error,
    refetch: fetchQuestion
  };
}
