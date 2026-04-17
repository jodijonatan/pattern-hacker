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

        // 🚀 RESET SESSION IF NEW GAME OR GAME OVER (Fix for B113)
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
            "difficulty" => $difficulty
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
            $game['score'] -= 5;
            $game['lives']--;
            $game['wrong']++;
            $game['correct'] = 0;
        }

        // 🔥 ADAPTIVE RULE (WAJIB LKS)
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

        // simpan ke DB jika selesai
        if ($gameOver) {
            $userId = $_SESSION['user']['id'];
            $finalScore = $game['score'];
            $finalDiff  = $game['difficulty'];
            
            // 1. Insert game record
            $this->table('scores')->insert([
                'user_id' => $userId,
                'score' => $finalScore,
                'difficulty' => $finalDiff
            ]);

            // 2. ATOMIC UPDATE: Prevent race conditions (B112)
            \LKSCore\Core\Database::query(
                "UPDATE `users` SET `total_score` = `total_score` + ? WHERE `id` = ?",
                [$finalScore, $userId]
            );

            // 3. Calculate Ranks
            $diffRankStmt = \LKSCore\Core\Database::query(
                "SELECT COUNT(*) + 1 as rank FROM `scores` WHERE `difficulty` = ? AND `score` > ?",
                [$finalDiff, $finalScore]
            );
            $difficultyRank = $diffRankStmt->fetch()['rank'];

            $globalRankStmt = \LKSCore\Core\Database::query(
                "SELECT COUNT(*) + 1 as rank FROM `scores` WHERE `score` > ?",
                [$finalScore]
            );
            $globalRank = $globalRankStmt->fetch()['rank'];

            $rank = [
                "difficulty" => $difficultyRank,
                "global" => $globalRank
            ];
        }

        return $this->json([
            "correct" => $correct,
            "state" => $game,
            "gameOver" => $gameOver,
            "rank" => $rank
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
    // PATTERN GENERATOR
    // =========================
    private function generatePattern($difficulty)
    {
        $type = ["arithmetic", "gap", "multi"][array_rand([0,1,2])];

        if ($type == "arithmetic") {
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

        if ($type == "gap") {
            $cur = rand(1, 10);
            $gap = rand(1, 3);

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
        $factor = rand(2, 2 + $difficulty);

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