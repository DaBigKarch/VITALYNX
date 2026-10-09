<?php
namespace App\Core;

class Router {
    private $routes = [];

    public function get($uri, $action) {
        $this->addRoute('GET', $uri, $action);
    }

    public function post($uri, $action) {
        $this->addRoute('POST', $uri, $action);
    }

    private function addRoute($method, $uri, $action) {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'action' => $action
        ];
    }

    public function dispatch() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && strpos($uri, $scriptName) === 0) {
            $uri = substr($uri, strlen($scriptName));
        }
        if ($uri === '') $uri = '/';

        foreach ($this->routes as $route) {
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $route['uri']);
            $pattern = '#^' . $pattern . '$#';
            
            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                $action = $route['action'];
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                // Merge into $_GET for backward compatibility and easy access
                $_GET = array_merge($_GET, $params);
                
                if (is_array($action)) {
                    $controller = new $action[0]();
                    $methodName = $action[1];
                    // Pass extracted named parameters in order
                    return call_user_func_array([$controller, $methodName], array_values($params));
                }
            }
        }
        
        http_response_code(404);
        echo "404 Not Found";
    }
}
