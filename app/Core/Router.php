<?php
declare(strict_types=1);

namespace App\Core;

use ReflectionMethod;
use ReflectionNamedType;

class Router
{
    private array $routes = [];

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
            // Static routes must win over placeholder routes regardless of
            // registration order, so /dashboard/links/reorder is never eaten
            // by /dashboard/links/{id}.
            'static' => !str_contains($path, '{'),
        ];
        return $this;
    }

    /**
     * Placeholders match a single non-slash segment. {id}-style names ending in
     * "id" are restricted to digits so numeric routes never swallow words.
     */
    private function compile(string $path): string
    {
        $pattern = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function (array $m): string {
            $name = $m[1];
            $charClass = preg_match('/(^|_)id$/i', $name) ? '\d+' : '[^/]+';
            return '(?P<' . $name . '>' . $charClass . ')';
        }, $path);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(string $method, string $uri): void
    {
        // HEAD is a GET without a body. Answering it with 404 breaks uptime
        // monitors, link previews and `curl -I`.
        $isHead = $method === 'HEAD';
        $lookupMethod = $isHead ? 'GET' : $method;

        if ($isHead) {
            ob_start();
        }

        $pathMatched = false;
        $allowed = [];

        // Static routes first, then placeholder routes.
        foreach ([true, false] as $staticPass) {
            foreach ($this->routes as $route) {
                if ($route['static'] !== $staticPass) {
                    continue;
                }
                if (!preg_match($route['pattern'], $uri, $matches)) {
                    continue;
                }
                $pathMatched = true;
                $allowed[$route['method']] = true;
                if ($route['method'] !== $lookupMethod) {
                    continue;
                }

                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

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
                call_user_func_array([$instance, $action], $this->coerce($controller, $action, $params));
                $this->finishHead($isHead);
                return;
            }
        }

        // Fall back to /{username} profile pages for GET/HEAD.
        if ($lookupMethod === 'GET' && preg_match('#^/([a-zA-Z0-9_]{3,32})$#', $uri, $m)) {
            $username = $m[1];
            $reserved = require BASE_PATH . '/config/reserved.php';
            if (!in_array(strtolower($username), $reserved, true)) {
                (new \App\Controllers\ProfileController())->show($username);
                $this->finishHead($isHead);
                return;
            }
        }

        if ($pathMatched) {
            http_response_code(405);
            header('Allow: ' . implode(', ', array_keys($allowed + ['HEAD' => true])));
            require BASE_PATH . '/resources/views/errors/405.php';
            $this->finishHead($isHead);
            return;
        }

        http_response_code(404);
        require BASE_PATH . '/resources/views/errors/404.php';
        $this->finishHead($isHead);
    }

    /**
     * Route params always arrive as strings. Controllers declare int/float
     * params, and strict_types would reject the raw string, so convert to the
     * declared scalar type before invoking.
     */
    private function coerce(string $controller, string $action, array $params): array
    {
        if (!$params) {
            return $params;
        }
        try {
            $ref = new ReflectionMethod($controller, $action);
        } catch (\ReflectionException) {
            return $params;
        }
        $types = [];
        foreach ($ref->getParameters() as $p) {
            $t = $p->getType();
            $types[$p->getName()] = $t instanceof ReflectionNamedType ? $t->getName() : null;
        }
        foreach ($params as $name => $value) {
            $target = $types[$name] ?? null;
            $params[$name] = match ($target) {
                'int' => (int)$value,
                'float' => (float)$value,
                'bool' => (bool)$value,
                default => $value,
            };
        }
        return $params;
    }

    private function finishHead(bool $isHead): void
    {
        if ($isHead) {
            $body = ob_get_clean();
            header('Content-Length: ' . strlen($body));
        }
    }
}
