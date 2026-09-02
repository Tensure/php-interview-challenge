<?php

namespace App;

use App\Support\Response;

class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler): void
    {
        $regex = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern);
        $this->routes[] = [
            'method' => strtoupper($method),
            'regex' => '#^' . $regex . '$#',
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $matchedPath = false;

        foreach ($this->routes as $route) {
            if (preg_match($route['regex'], $path, $matches)) {
                $matchedPath = true;

                if ($route['method'] !== strtoupper($method)) {
                    continue;
                }

                $params = array_filter(
                    $matches,
                    fn ($key) => !is_int($key),
                    ARRAY_FILTER_USE_KEY
                );

                ($route['handler'])($params);
                return;
            }
        }

        if ($matchedPath) {
            Response::error('Method not allowed', 405);
            return;
        }

        Response::error('Not found', 404);
    }
}
