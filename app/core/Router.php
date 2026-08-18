<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $uri, string $action): void
    {
        $this->add('GET', $uri, $action);
    }

    public function post(string $uri, string $action): void
    {
        $this->add('POST', $uri, $action);
    }

    private function add(string $method, string $uri, string $action): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'uri' => trim($uri, '/'),
            'action' => $action,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = trim((string) parse_url($uri, PHP_URL_PATH), '/');
        $base = trim((string) config('app_url', ''), '/');
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = trim(substr($path, strlen($base)), '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }
            $params = $this->match($route['uri'], $path);
            if ($params !== false) {
                $this->call($route['action'], $params);
                return;
            }
        }

        http_response_code(404);
        (new \App\Controllers\ErrorController())->notFound();
    }

    private function match(string $pattern, string $path): array|false
    {
        if ($pattern === '' && $path === '') {
            return [];
        }
        $regex = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $pattern);
        if ($regex === null) {
            return false;
        }
        if (preg_match('#^' . $regex . '$#', $path, $matches)) {
            array_shift($matches);
            return $matches;
        }
        return false;
    }

    private function call(string $action, array $params): void
    {
        [$class, $method] = explode('@', $action);
        $controller = new $class();
        call_user_func_array([$controller, $method], $params);
    }
}
