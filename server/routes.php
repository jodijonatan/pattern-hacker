<?php

return [
    // Auth
    "POST /auth/register" => "AuthController@register",
    "POST /auth/login"    => "AuthController@login",
    "POST /auth/logout"   => "AuthController@logout",
    "GET /auth/me"        => "AuthController@me",

    // Game
    "POST /generate-question" => "GameController@generateQuestion",
    "POST /submit-answer"     => "GameController@submitAnswer",
    "POST /end-game"          => "GameController@endGame",
    "POST /reset-game"        => "GameController@resetGame",
    "GET /get-score"          => "GameController@getScore",

    // Leaderboard
    "GET /leaderboard" => "LeaderboardController@getLeaderboard",
];
