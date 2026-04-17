<?php

namespace LKSCore\Controllers;

use LKSCore\Core\Database;

class AuthController
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

    public function register()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if ($username === '' || $password === '') {
            return $this->json(["error" => "Invalid input"], 400);
        }

        // cek duplicate (FIXED: Use query builder)
        $exist = $this->table('users')
            ->where('username', $username)
            ->first();

        if ($exist) {
            return $this->json(["error" => "Username already used"], 409);
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        // FIXED: Use query builder insert
        $this->table('users')->insert([
            'username' => $username, 
            'password' => $hash, 
            'total_score' => 0
        ]);

        return $this->json(["message" => "Register success"]);
    }

    public function login()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $username = $data['username'] ?? '';

        $user = $this->table('users')
            ->where('username', $username)
            ->first();

        if (!$user || !password_verify($data['password'] ?? '', $user['password'])) {
            return $this->json(["error" => "Invalid credentials"], 401);
        }

        $_SESSION['user'] = [
            "id" => $user['id'],
            "username" => $user['username']
        ];

        return $this->json(["message" => "Login success", "user" => $_SESSION['user']]);
    }

    public function logout()
    {
        session_destroy();
        return $this->json(["message" => "Logout success"]);
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