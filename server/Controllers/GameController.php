<?php

namespace LKSCore\Controllers;

use LKSCore\Core\Database;

class GameController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function table($tableName)
    {
        return \LKSCore\Core\Database::table($tableName);
    }

    // =========================
    // GENERATE QUESTION (MODUL A + B)
    // =========================
    public function generateQuestion()
    {
        $this->guard();

        // RESET SESSION IF NEW GAME OR GAME OVER
        if (!isset($_SESSION['game']) || ($_SESSION['game']['lives'] ?? 3) <= 0) {
            $_SESSION['game'] = [
                "score" => 0,
                "lives" => 3,
                "difficulty" => 1,
                "correct" => 0,
                "wrong" => 0
            ];
        }

        $difficulty = $_SESSION['game']['difficulty'] ?? 1;

        $q = $this->generatePattern($difficulty);

        $_SESSION['answer'] = $q['answer'];
        unset($q['answer']);

        return $this->json([
            "question" => $q['sequence'],
            "type" => $q['type'],
            "difficulty" => $difficulty,
            "state" => $_SESSION['game']
        ]);
    }

    // =========================
    // SUBMIT ANSWER (ADAPTIVE CORE)
    // =========================
    public function submitAnswer()
    {
        $this->guard();

        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['answer']) || !is_numeric($input['answer'])) {
            return $this->json(["error" => "Invalid input"], 400);
        }

        if (!isset($_SESSION['game'])) {
            $_SESSION['game'] = [
                "score" => 0,
                "lives" => 3,
                "difficulty" => 1,
                "correct" => 0,
                "wrong" => 0
            ];
        }

        $game =& $_SESSION['game'];
        $correct = ((int)$input['answer'] === $_SESSION['answer']);

        if ($correct) {
            $game['score'] += 10;
            $game['correct']++;
            $game['wrong'] = 0;
        } else {
            // FIX: Clamp score to minimum 0 — prevents negative leaderboard entries
            $game['score'] = max(0, $game['score'] - 5);
            $game['lives']--;
            $game['wrong']++;
            $game['correct'] = 0;
        }

        // ADAPTIVE DIFFICULTY RULE
        if ($game['correct'] >= 3) {
            $game['difficulty']++;
            $game['correct'] = 0;
        }

        if ($game['wrong'] >= 2 && $game['difficulty'] > 1) {
            $game['difficulty']--;
            $game['wrong'] = 0;
        }

        $gameOver = $game['lives'] <= 0;

        $rank = null;

        // Save to DB if game is over
        if ($gameOver) {
            $rank = $this->saveGameAndGetRanks();
        }

        return $this->json([
            "correct" => $correct,
            "state" => $game,
            "gameOver" => $gameOver,
            "rank" => $rank
        ]);
    }

    // =========================
    // END GAME (for timer-based game over)
    // FIX: Timer expiry now saves the score
    // =========================
    public function endGame()
    {
        $this->guard();

        if (!isset($_SESSION['game'])) {
            return $this->json(["error" => "No active game session"], 400);
        }

        $game =& $_SESSION['game'];

        // Only end if game is actually still active
        if ($game['lives'] <= 0) {
            return $this->json(["error" => "Game already over"], 400);
        }

        // Force end the game
        $game['lives'] = 0;

        $rank = $this->saveGameAndGetRanks();

        return $this->json([
            "state" => $game,
            "gameOver" => true,
            "rank" => $rank
        ]);
    }

    // =========================
    // RESET GAME (force-clear session)
    // FIX: Prevents stale state when restarting mid-game
    // =========================
    public function resetGame()
    {
        $this->guard();

        $_SESSION['game'] = [
            "score" => 0,
            "lives" => 3,
            "difficulty" => 1,
            "correct" => 0,
            "wrong" => 0
        ];
        unset($_SESSION['answer']);

        return $this->json([
            "message" => "Game reset",
            "state" => $_SESSION['game']
        ]);
    }

    // =========================
    // SCORE ENDPOINT
    // =========================
    public function getScore()
    {
        $this->guard();

        return $this->json([
            "score" => $_SESSION['game']['score'] ?? 0
        ]);
    }

    // =========================
    // SHARED: Save game + calculate ranks
    // =========================
    private function saveGameAndGetRanks()
    {
        $userId    = $_SESSION['user']['id'];
        $game      = $_SESSION['game'];
        $finalScore = max(0, $game['score']); // Extra safety clamp
        $finalDiff  = $game['difficulty'];

        // 1. Insert game record
        $this->table('scores')->insert([
            'user_id' => $userId,
            'score' => $finalScore,
            'difficulty' => $finalDiff
        ]);

        // 2. ATOMIC UPDATE: Prevent race conditions
        \LKSCore\Core\Database::query(
            "UPDATE `users` SET `total_score` = `total_score` + ? WHERE `id` = ?",
            [$finalScore, $userId]
        );

        // 3. Calculate Ranks
        $diffRankStmt = \LKSCore\Core\Database::query(
            "SELECT COUNT(*) + 1 as `rank` FROM `scores` WHERE `difficulty` = ? AND `score` > ?",
            [$finalDiff, $finalScore]
        );
        $difficultyRank = $diffRankStmt->fetch()['rank'];

        $globalRankStmt = \LKSCore\Core\Database::query(
            "SELECT COUNT(*) + 1 as `rank` FROM `scores` WHERE `score` > ?",
            [$finalScore]
        );
        $globalRank = $globalRankStmt->fetch()['rank'];

        return [
            "difficulty" => $difficultyRank,
            "global" => $globalRank
        ];
    }

    // =========================
    // PATTERN GENERATOR (FIXED)
    // =========================
    private function generatePattern($difficulty)
    {
        // FIX: Cleaner random selection
        $types = ["arithmetic", "gap", "multi"];
        $type = $types[array_rand($types)];

        if ($type === "arithmetic") {
            $start = rand(1, 10);
            $step = rand(1, 5 + $difficulty);

            $seq = [];
            for ($i = 0; $i < 4; $i++) {
                $seq[] = $start + ($i * $step);
            }

            return [
                "sequence" => $seq,
                "answer" => $start + 4 * $step,
                "type" => $type
            ];
        }

        if ($type === "gap") {
            $cur = rand(1, 10);
            // FIX: Difficulty now scales the gap pattern
            $gap = rand(1, 3 + intval($difficulty / 2));

            $seq = [$cur];

            for ($i = 0; $i < 3; $i++) {
                $cur += $gap;
                $seq[] = $cur;
                $gap++;
            }

            return [
                "sequence" => $seq,
                "answer" => $cur + $gap,
                "type" => $type
            ];
        }

        // multiplication
        $start = rand(1, 5);
        // FIX: Cap factor to prevent absurdly large numbers at high difficulty
        $factor = rand(2, min(2 + $difficulty, 5));

        $seq = [];
        for ($i = 0; $i < 4; $i++) {
            $seq[] = $start * pow($factor, $i);
        }

        return [
            "sequence" => $seq,
            "answer" => $start * pow($factor, 4),
            "type" => $type
        ];
    }

    // =========================
    // SECURITY GUARD
    // =========================
    private function guard()
    {
        if (!isset($_SESSION['user'])) {
            $this->json(["error" => "Unauthorized"], 401);
        }
    }

    private function json($payload, $code = 200)
    {
        http_response_code($code);
        header("Content-Type: application/json");
        
        $response = [
            "success" => $code < 400,
            "data" => $code < 400 ? $payload : null
        ];

        if ($code >= 400) {
            $response["message"] = is_array($payload) ? ($payload["error"] ?? "Error") : $payload;
        }
        
        echo json_encode($response);
        exit;
    }
}