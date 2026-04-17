<?php

return [
    "POST /auth/register" => "AuthController@register",
    "POST /auth/login" => "AuthController@login",
    "POST /auth/logout" => "AuthController@logout",

    "POST /generate-question" => "GameController@generateQuestion",
    "POST /submit-answer" => "GameController@submitAnswer",

    "GET /get-score" => "GameController@getScore",
    "GET /leaderboard" => "LeaderboardController@getLeaderboard",
];
