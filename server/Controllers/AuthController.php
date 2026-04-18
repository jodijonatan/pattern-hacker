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

    // =========================
    // REGISTER
    // =========================
    public function register()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        // Input validation
        if ($username === '' || $password === '') {
            return $this->json(["error" => "Username and password are required"], 400);
        }

        // FIX: Username length validation
        if (strlen($username) < 3 || strlen($username) > 30) {
            return $this->json(["error" => "Username must be 3-30 characters"], 400);
        }

        // FIX: Username format — alphanumeric + underscore only
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return $this->json(["error" => "Username can only contain letters, numbers, and underscores"], 400);
        }

        // FIX: Password strength validation
        if (strlen($password) < 6) {
            return $this->json(["error" => "Password must be at least 6 characters"], 400);
        }

        // Check duplicate
        $exist = $this->table('users')
            ->where('username', $username)
            ->first();

        if ($exist) {
            return $this->json(["error" => "Username already taken"], 409);
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $this->table('users')->insert([
            'username' => $username,
            'password' => $hash,
            'total_score' => 0
        ]);

        return $this->json(["message" => "Registration successful"]);
    }

    // =========================
    // LOGIN
    // =========================
    public function login()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if ($username === '' || $password === '') {
            return $this->json(["error" => "Username and password are required"], 400);
        }

        $user = $this->table('users')
            ->where('username', $username)
            ->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->json(["error" => "Invalid credentials"], 401);
        }

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        $_SESSION['user'] = [
            "id" => $user['id'],
            "username" => $user['username']
        ];

        // Generate fresh CSRF token on login
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        return $this->json([
            "message" => "Login successful",
            "user" => $_SESSION['user'],
            "csrf_token" => $_SESSION['csrf_token']
        ]);
    }

    // =========================
    // SESSION CHECK (GET /auth/me)
    // FIX: Validates session is still alive on page refresh
    // =========================
    public function me()
    {
        if (!isset($_SESSION['user'])) {
            return $this->json(["error" => "Not authenticated"], 401);
        }

        return $this->json([
            "user" => $_SESSION['user'],
            "csrf_token" => $_SESSION['csrf_token'] ?? null
        ]);
    }

    // =========================
    // LOGOUT
    // =========================
    public function logout()
    {
        // Clear session data
        $_SESSION = [];

        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();

        return $this->json(["message" => "Logout successful"]);
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