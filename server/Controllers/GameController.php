<?php

namespace LKSCore\Controllers;

use LKSCore\Core\Database;

class GameController extends BaseController
{
    public function generateQuestion()
    {
        $this->guard();

        // Initialize session if not exists or game over
        if (!isset($_SESSION['game']) || ($_SESSION['game']['lives'] ?? 3) <= 0) {
            $_SESSION['game'] = [
                'score' => 0,
                'lives' => 3,
                'difficulty' => 1,
                'streak' => 0,
                'start_time' => time()
            ];
        }

        $difficulty = $_SESSION['game']['difficulty'];
        $pattern = $this->generatePattern($difficulty);

        $_SESSION['game']['answer'] = $pattern['answer'];

        $this->json([
            'question' => $pattern['question'],
            'state' => [
                'score' => $_SESSION['game']['score'],
                'lives' => $_SESSION['game']['lives'],
                'difficulty' => $_SESSION['game']['difficulty']
            ]
        ]);
    }

    public function submitAnswer()
    {
        $this->guard();

        $answer = $_POST['answer'] ?? null;
        $correctAnswer = $_SESSION['game']['answer'] ?? null;

        if ($answer === null) {
            $this->json(['message' => 'Answer required'], 400);
        }

        $isCorrect = (string)$answer === (string)$correctAnswer;

        if ($isCorrect) {
            $_SESSION['game']['score'] += 10;
            $_SESSION['game']['streak']++;
            // Difficulty increases every 3 correct answers
            if ($_SESSION['game']['streak'] >= 3) {
                $_SESSION['game']['difficulty'] = min(10, $_SESSION['game']['difficulty'] + 1);
                $_SESSION['game']['streak'] = 0;
            }
        } else {
            $_SESSION['game']['score'] -= 5;
            $_SESSION['game']['lives']--;
            $_SESSION['game']['streak'] = 0;
            // Difficulty decreases on mistake to keep it balanced
            if ($_SESSION['game']['difficulty'] > 1) {
                $_SESSION['game']['difficulty']--;
            }
        }

        // Clamp score to >= 0
        $_SESSION['game']['score'] = max(0, $_SESSION['game']['score']);

        $gameOver = $_SESSION['game']['lives'] <= 0;
        $rank = null;

        if ($gameOver) {
            $rank = $this->saveGameAndGetRanks();
        }

        $this->json([
            'correct' => $isCorrect,
            'correctAnswer' => $correctAnswer,
            'gameOver' => $gameOver,
            'state' => [
                'score' => $_SESSION['game']['score'],
                'lives' => $_SESSION['game']['lives'],
                'difficulty' => $_SESSION['game']['difficulty']
            ],
            'rank' => $rank
        ]);
    }

    public function endGame()
    {
        $this->guard();
        
        // Force game over for timer-based expiry
        $_SESSION['game']['lives'] = 0; 
        $rank = $this->saveGameAndGetRanks();

        $this->json([
            'message' => 'Game forced to end',
            'state' => [
                'score' => $_SESSION['game']['score'],
                'lives' => 0,
                'difficulty' => $_SESSION['game']['difficulty']
            ],
            'rank' => $rank
        ]);
    }

    public function resetGame()
    {
        $this->guard();
        unset($_SESSION['game']);
        $this->json(['message' => 'Game state reset']);
    }

    private function saveGameAndGetRanks()
    {
        $userId = $_SESSION['user']['id'];
        $score = $_SESSION['game']['score'] ?? 0;
        $difficulty = $_SESSION['game']['difficulty'] ?? 1;

        // Save score if not empty (prevent saving empty 0 score games if preferred, 
        // but typically we save all final attempts)
        $this->table('scores')->insert([
            'user_id' => $userId,
            'score' => $score,
            'difficulty' => $difficulty
        ]);

        return $this->getCurrentRanks($score, $difficulty);
    }

    /**
     * Calculates the real-time rank of the current score
     */
    private function getCurrentRanks($score, $difficulty)
    {
        // Global Rank: count distinct scores higher than this one
        $globalRankQuery = Database::query(
            "SELECT COUNT(DISTINCT score) + 1 as rank FROM scores WHERE score > ?", 
            [$score]
        )->fetch();

        // Difficulty Rank: count distinct scores higher than this one in the same level
        $difficultyRankQuery = Database::query(
            "SELECT COUNT(DISTINCT score) + 1 as rank FROM scores WHERE score > ? AND difficulty = ?", 
            [$score, $difficulty]
        )->fetch();

        return [
            'global' => (int)($globalRankQuery['rank'] ?? 1),
            'difficulty' => (int)($difficultyRankQuery['rank'] ?? 1)
        ];
    }

    private function generatePattern($level)
    {
        $types = ['linear', 'multi', 'fib', 'gap'];
        $type = $types[array_rand($types)];

        $length = 4 + floor($level / 2);
        $start = rand(1, 10 + $level);
        $diff = rand(2, 5 + $level);

        $sequence = [];
        $answer = 0;

        switch ($type) {
            case 'linear':
                for ($i = 0; $i < $length; $i++) $sequence[] = $start + ($i * $diff);
                $answer = $start + ($length * $diff);
                break;
            case 'multi':
                $diff = rand(2, 3);
                for ($i = 0; $i < $length; $i++) $sequence[] = $start * pow($diff, $i);
                $answer = $start * pow($diff, $length);
                break;
            case 'fib':
                $sequence = [$start, $start + $diff];
                for ($i = 2; $i < $length; $i++) $sequence[] = $sequence[$i - 1] + $sequence[$i - 2];
                $answer = $sequence[$length - 1] + $sequence[$length - 2];
                break;
            case 'gap':
                $gapSize = 1;
                $current = $start;
                $gapIncrement = floor($level / 3);
                for ($i = 0; $i < $length; $i++) {
                    $sequence[] = $current;
                    $current += ($diff + $gapSize);
                    $gapSize += $gapIncrement;
                }
                $answer = $current;
                break;
        }

        return [
            'question' => $sequence,
            'answer' => $answer
        ];
    }
}