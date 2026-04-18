<?php

namespace LKSCore\Controllers;

use LKSCore\Core\Database;

abstract class BaseController
{
    /**
     * Helper to return JSON response
     */
    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
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
