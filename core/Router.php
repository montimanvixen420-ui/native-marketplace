<?php

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][] = ['path' => $path, 'handler' => $handler];
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][] = ['path' => $path, 'handler' => $handler];
    }

    public function resolve(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove trailing slash (unless it's just root "/")
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        $routesForMethod = $this->routes[$method] ?? [];

        foreach ($routesForMethod as $route) {
            $params = $this->match($route['path'], $uri);

            if ($params === null) {
                continue; // this route doesn't match, try the next one
            }

            [$controllerClass, $methodName] = $route['handler'];

            $controllerFile = __DIR__ . "/../controllers/{$controllerClass}.php";
            if (!file_exists($controllerFile)) {
                die("Controller file na '{$controllerClass}.php' ay hindi nahanap.");
            }
            require_once $controllerFile;

            $controller = new $controllerClass();
            $controller->$methodName(...$params);
            return;
        }

        http_response_code(404);
        echo "404 — Hindi nahanap ang page na ito: {$uri}";
    }

    /**
     * Tries to match a route pattern (which may contain {id} placeholders)
     * against the actual URI. If it matches, returns the array of extracted
     * params in order. If it doesn't match, returns null.
     */
    private function match(string $routePath, string $uri): ?array
    {
        // Convert {placeholder} segments into regex capture groups
        $pattern = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $uri, $matches)) {
            return null;
        }

        array_shift($matches); // remove the full-string match, keep only params

        return $matches;
    }
}