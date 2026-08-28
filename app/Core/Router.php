<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $named = [];

    public function get(string $path, array $handler, array $middleware = []): self
    {
        return $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): self
    {
        return $this->add('POST', $path, $handler, $middleware);
    }

    public function put(string $path, array $handler, array $middleware = []): self
    {
        return $this->add('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, array $handler, array $middleware = []): self
    {
        return $this->add('DELETE', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, array $handler, array $middleware): self
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
            'pattern' => $this->compile($path),
        ];
        return $this;
    }

    private function compile(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(string $method, string $uri): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middleware
                foreach ($route['middleware'] as $mw) {
                    if (is_string($mw)) {
                        $mwClass = "App\\Middleware\\$mw";
                        (new $mwClass())->handle();
                    } elseif (is_callable($mw)) {
                        $mw();
                    }
                }

                [$controller, $action] = $route['handler'];
                $instance = new $controller();
                call_user_func_array([$instance, $action], $params);
                return;
            }
        }

        // Try profile username as fallback for GET
        if ($method === 'GET' && preg_match('#^/([a-zA-Z0-9_]{3,32})$#', $uri, $m)) {
            $username = $m[1];
            $reserved = require BASE_PATH . '/config/reserved.php';
            if (!in_array(strtolower($username), $reserved, true)) {
                $controller = new \App\Controllers\ProfileController();
                $controller->show($username);
                return;
            }
        }

        http_response_code(404);
        require BASE_PATH . '/resources/views/errors/404.php';
    }
}
