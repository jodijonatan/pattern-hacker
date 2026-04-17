<?php

namespace LKSCore\Controllers;

use LKSCore\Core\Database;

class LeaderboardController
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

    public function getLeaderboard()
    {
        $difficulty = $_GET['difficulty'] ?? null;

        $query = $this->table('scores')
            ->select(['users.username', 'scores.score', 'scores.difficulty', 'scores.created_at'])
            ->join('users', 'scores.user_id', '=', 'users.id')
            ->orderBy('scores.score', 'DESC')
            ->limit(10);
            
        if ($difficulty) {
            $query = $query->where('scores.difficulty', $difficulty);
        }
        
        $data = $query->all();

        return $this->json([
            "leaderboard" => $data
        ]);
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