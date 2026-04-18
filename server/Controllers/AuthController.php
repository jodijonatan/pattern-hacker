<?php

namespace LKSCore\Controllers;

class AuthController extends BaseController
{
    /**
     * GET /auth/me
     */
    public function me()
    {
        if (isset($_SESSION['user'])) {
            $this->json([
                'user' => $_SESSION['user'],
                'csrf_token' => $_SESSION['csrf_token'] ?? null
            ]);
        }
        $this->json(['message' => 'Unauthorized'], 401);
    }

    /**
     * POST /auth/login
     */
    public function login()
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->json(['message' => 'Credentials required'], 400);
        }

        $user = $this->table('users')->where('username', $username)->first();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true); // FIX: Prevent session fixation
            
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username']
            ];

            // FIX: Generate CSRF token on login
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            $this->json([
                'user' => $_SESSION['user'],
                'csrf_token' => $_SESSION['csrf_token']
            ]);
        }

        $this->json(['message' => 'Invalid credentials'], 401);
    }

    /**
     * POST /auth/register
     */
    public function register()
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $this->json(['message' => 'Invalid username format'], 400);
        }

        if (strlen($password) < 6) {
            $this->json(['message' => 'Password must be at least 6 characters'], 400);
        }

        $exists = $this->table('users')->where('username', $username)->first();
        if ($exists) {
            $this->json(['message' => 'Username already taken'], 400);
        }

        $this->table('users')->insert([
            'username' => $username,
            'password' => password_hash($password, PASSWORD_BCRYPT)
        ]);

        $this->json(['message' => 'Registration successful']);
    }

    /**
     * POST /auth/logout
     */
    public function logout()
    {
        session_destroy();
        $this->json(['message' => 'Logged out']);
    }
}