<?php
/**
 * Pattern Hacker Backend - FIXED ROUTING + AUTH
 */

// Global CORS first
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Load dependencies FIRST (CRITICAL FIX)
require_once 'autoload.php';
require_once 'config.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// SAFE DB INIT - Autoloader fully loaded
\LKSCore\Core\Database::init([
    'host' => 'localhost',
    'db' => 'lks_game_db',
    'user' => 'root',
    'pass' => ''
]);

$routes = require 'routes.php';

// Routing logic (now autoloader works!)
$method = $_SERVER['REQUEST_METHOD'];
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
if (empty($path)) $path = '/';

foreach ($routes as $pattern => $callback) {
    [$routeMethod, $routePath] = explode(' ', $pattern, 2);
    $cleanRoute = trim($routePath, '/');
    if (empty($cleanRoute)) $cleanRoute = '/';
    
    if ($routeMethod === $method && $cleanRoute === $path) {
        [$controller, $methodName] = explode('@', $callback);
        // FIXED: Use fully qualified namespace for namespaced controllers
        $controllerClass = 'LKSCore\\Controllers\\' . $controller;
        $controllerInstance = new $controllerClass();
        $controllerInstance->$methodName();
        exit;
    }
}

// Always JSON, never HTML errors (Frontend fix)
header('Content-Type: application/json');
http_response_code(404);
echo json_encode(['success' => false, 'error' => 'Endpoint not found: ' . $method . ' ' . $path]);
exit;
?>

