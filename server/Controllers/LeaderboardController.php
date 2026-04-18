<?php

namespace LKSCore\Controllers;

class LeaderboardController extends BaseController
{
    public function index()
    {
        $difficulty = $_GET['difficulty'] ?? null;
        
        $query = $this->table('scores')
            ->select(['users.username', 'scores.score', 'scores.difficulty', 'scores.created_at'])
            ->join('users', 'users.id', '=', 'scores.user_id');

        if ($difficulty) {
            $query->where('difficulty', (int)$difficulty);
        }

        $rankings = $query->orderBy('scores.score', 'DESC')
            ->limit(10)
            ->get();

        $this->json(['leaderboard' => $rankings]);
    }
}