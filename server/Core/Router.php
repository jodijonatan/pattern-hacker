<?php
namespace LKSCore\Core;

class Router
{
    public static function handleCORS() {
        // Already handled in index.php
    }

    public static function run($routes) {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = trim($path, '/');
        if (empty($path)) $path = '/';
        
        foreach ($routes as $pattern => $callback) {
            [$routeMethod, $routePath] = explode(' ', $pattern, 2);
            $cleanRoute = trim($routePath, '/');
            if (empty($cleanRoute)) $cleanRoute = '/';
            
            if ($routeMethod === $method && $cleanRoute === $path) {
                if (strpos($callback, '@') !== false) {
                    [$controller, $methodName] = explode('@', $callback);
                    $controllerClass = "LKSCore\\\\Controllers\\\\{$controller}";
                    $controllerInstance = new $controllerClass();
                    return $controllerInstance->$methodName();
                }
            }
        }
        
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['error' => "Endpoint not found: $method $path"]);
        exit;
    }
}
?>

