<?php

namespace LKSCore\Controllers;

use LKSCore\Core\Database;

class GameController extends BaseController
{
    public function generateQuestion()
    {
        $this->guard();

        // FIX: resetGame() already unsets this, but we preserve the check
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
            'score' => $_SESSION['game']['score'],
            'lives' => $_SESSION['game']['lives'],
            'difficulty' => $_SESSION['game']['difficulty']
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
            if ($_SESSION['game']['streak'] >= 3) {
                $_SESSION['game']['difficulty'] = min(10, $_SESSION['game']['difficulty'] + 1);
                $_SESSION['game']['streak'] = 0;
            }
        } else {
            $_SESSION['game']['lives']--;
            $_SESSION['game']['streak'] = 0;
            // FIX: Difficulty can decrease on mistake to keep it balanced
            if ($_SESSION['game']['difficulty'] > 1) {
                $_SESSION['game']['difficulty']--;
            }
        }

        // Clamp score to >= 0
        $_SESSION['game']['score'] = max(0, $_SESSION['game']['score']);

        $gameOver = $_SESSION['game']['lives'] <= 0;
        $rankings = null;

        if ($gameOver) {
            $rankings = $this->saveGameAndGetRanks();
        }

        $this->json([
            'correct' => $isCorrect,
            'correctAnswer' => $correctAnswer,
            'gameOver' => $gameOver,
            'score' => $_SESSION['game']['score'],
            'lives' => $_SESSION['game']['lives'],
            'difficulty' => $_SESSION['game']['difficulty'],
            'rankings' => $rankings
        ]);
    }

    public function endGame()
    {
        $this->guard();
        
        // FIX: Force game over for timer-based expiry
        $_SESSION['game']['lives'] = 0; 
        $rankings = $this->saveGameAndGetRanks();

        $this->json([
            'message' => 'Game forced to end',
            'score' => $_SESSION['game']['score'],
            'rankings' => $rankings
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
        $score = $_SESSION['game']['score'];
        $difficulty = $_SESSION['game']['difficulty'];

        // Save score
        $this->table('scores')->insert([
            'user_id' => $userId,
            'score' => $score,
            'difficulty' => $difficulty
        ]);

        // Get top rankings
        return $this->table('scores')
            ->select(['users.username', 'scores.score', 'scores.difficulty'])
            ->join('users', 'users.id', '=', 'scores.user_id')
            ->orderBy('scores.score', 'DESC')
            ->limit(5)
            ->get();
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
                break;
            case 'multi':
                $diff = rand(2, 3); // FIX: Keep multipliers low to avoid overflow
                for ($i = 0; $i < $length; $i++) $sequence[] = $start * pow($diff, $i);
                break;
            case 'fib':
                $sequence = [$start, $start + $diff];
                for ($i = 2; $i < $length; $i++) $sequence[] = $sequence[$i - 1] + $sequence[$i - 2];
                break;
            case 'gap':
                // Linear with increasing gap
                $gap = 1;
                $current = $start;
                for ($i = 0; $i < $length; $i++) {
                    $sequence[] = $current;
                    $current += ($diff + $gap);
                    $gap += floor($level / 3); // FIX: Scaling gap difficulty
                }
                break;
        }

        $answerIndex = array_rand($sequence);
        $answer = $sequence[$answerIndex];
        $sequence[$answerIndex] = '?';

        return [
            'question' => implode(', ', $sequence),
            'answer' => $answer
        ];
    }
}