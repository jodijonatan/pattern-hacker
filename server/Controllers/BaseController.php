<?php

namespace LKSCore\Controllers;

use LKSCore\Core\Database;

abstract class BaseController
{
    /**
     * Helper to return JSON response
     */
    protected function json($payload, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        
        $response = [
            "success" => $status < 400,
            "data" => $status < 400 ? $payload : null
        ];
        
        if ($status >= 400) {
            $response["message"] = is_array($payload) ? ($payload["message"] ?? $payload["error"] ?? "Error") : $payload;
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Helper to get database table instance
     */
    protected function table($name)
    {
        return Database::table($name);
    }

    /**
     * Guard: Ensure user is authenticated
     */
    protected function guard()
    {
        if (!isset($_SESSION['user'])) {
            $this->json(['message' => 'Unauthorized'], 401);
        }
    }
}
