<?php

namespace LKSCore\Controllers;

class LeaderboardController extends BaseController
{
    public function index()
    {
        // FIX: Ensure difficulty is handled as null if not provided or 'null' string
        $difficulty = $_GET['difficulty'] ?? null;
        if ($difficulty === 'null' || $difficulty === '') $difficulty = null;
        
        $query = $this->table('scores')
            ->select([
                'users.username', 
                'MAX(scores.score) as score', 
                'MAX(scores.difficulty) as difficulty', 
                'MAX(scores.created_at) as created_at'
            ])
            // Using LEFT JOIN for resilience
            ->join('users', 'users.id', '=', 'scores.user_id')
            ->groupBy('scores.user_id');

        if ($difficulty !== null) {
            // FIX: Be explicit with table name to avoid ambiguity
            $query->where('scores.difficulty', (int)$difficulty);
        }

        $rankings = $query->orderBy('score', 'DESC')
            ->limit(10)
            ->get();

        $this->json(['leaderboard' => $rankings]);
    }
}