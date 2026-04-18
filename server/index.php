<?php
/**
 * Pattern Hacker Backend — Entry Point
 * 
 * Fixes applied:
 * - Dynamic CORS origin from allowlist (production-safe)
 * - Secure session configuration (HttpOnly, SameSite)
 * - DB credentials sourced from config.php constants (single source of truth)
 * - CSRF token validation for state-mutating requests
 */

// ========================================
// 1. CORS — Dynamic Origin Allowlist
// ========================================
$allowedOrigins = [
    'http://localhost:5173',
    'http://localhost:3000',
    'http://127.0.0.1:5173',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // In production (same-origin), no CORS header needed.
    // If you deploy to a custom domain, add it to $allowedOrigins above.
}

header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-CSRF-Token");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ========================================
// 2. Autoloader + Config (single require)
// ========================================
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/config.php';

// ========================================
// 3. Secure Session
// ========================================
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly'  => true,
        'cookie_samesite'  => 'Lax',   // 'Strict' blocks cross-origin redirects; Lax is safer for usability
        'cookie_secure'    => false,    // Set to true when serving over HTTPS in production
        'use_strict_mode'  => true,
        'cookie_lifetime'  => 0,        // Session cookie (expires when browser closes)
    ]);
}

// ========================================
// 4. Database Init — from config.php constants
// ========================================
\LKSCore\Core\Database::init([
    'host' => DB_HOST,
    'db'   => DB_NAME,
    'user' => DB_USER,
    'pass' => DB_PASS
]);

// ========================================
// 5. CSRF Token Management
// ========================================
// Generate token if none exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate CSRF on all POST requests (except login/register which create sessions)
$csrfExemptRoutes = ['auth/login', 'auth/register'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    
    if (!in_array($currentPath, $csrfExemptRoutes, true)) {
        $csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfHeader)) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }
    }
}

// ========================================
// 6. Routing
// ========================================
$routes = require __DIR__ . '/routes.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
if (empty($path)) $path = '/';

foreach ($routes as $pattern => $callback) {
    [$routeMethod, $routePath] = explode(' ', $pattern, 2);
    $cleanRoute = trim($routePath, '/');
    if (empty($cleanRoute)) $cleanRoute = '/';

    if ($routeMethod === $method && $cleanRoute === $path) {
        [$controller, $methodName] = explode('@', $callback);
        $controllerClass = 'LKSCore\\Controllers\\' . $controller;
        $controllerInstance = new $controllerClass();
        $controllerInstance->$methodName();
        exit;
    }
}

// ========================================
// 7. 404 Fallback — always JSON
// ========================================
header('Content-Type: application/json');
http_response_code(404);
echo json_encode([
    'success' => false,
    'message' => 'Endpoint not found: ' . $method . ' /' . $path
]);
exit;
