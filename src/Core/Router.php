<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $namedRoutes = [];
    private array $middlewares = [];
    private string $prefix = '';
    private array $groupMiddleware = [];

    public function get(string $path, callable $handler, string $name = ''): self
    {
        return $this->addRoute('GET', $path, $handler, $name);
    }

    public function post(string $path, callable $handler, string $name = ''): self
    {
        return $this->addRoute('POST', $path, $handler, $name);
    }

    public function put(string $path, callable $handler, string $name = ''): self
    {
        return $this->addRoute('PUT', $path, $handler, $name);
    }

    public function delete(string $path, callable $handler, string $name = ''): self
    {
        return $this->addRoute('DELETE', $path, $handler, $name);
    }

    public function group(array $attributes, callable $callback): void
    {
        $oldPrefix = $this->prefix;
        $oldMiddleware = $this->groupMiddleware;

        $newPrefix = trim($this->prefix . '/' . trim($attributes['prefix'] ?? '', '/'), '/');
        $this->prefix = $newPrefix;
        $this->groupMiddleware = array_merge($this->groupMiddleware, $attributes['middleware'] ?? []);

        $callback($this);

        $this->prefix = $oldPrefix;
        $this->groupMiddleware = $oldMiddleware;
    }

    private function addRoute(string $method, string $path, callable $handler, string $name): self
    {
        $fullPath = trim($this->prefix . '/' . ltrim($path, '/'), '/');
        $middlewares = $this->groupMiddleware;

        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];

        if ($name) {
            $this->namedRoutes[$name] = $fullPath;
        }

        return $this;
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri = $request->path();

        if ($method === 'POST' && $request->input('_method')) {
            $method = strtoupper($request->input('_method'));
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchRoute($route['path'], $uri);
            if ($params !== false) {
                foreach ($route['middlewares'] as $middleware) {
                    $middlewareInstance = App::make($middleware);
                    $result = $middlewareInstance->handle($request);
                    if ($result === false) {
                        return;
                    }
                }

                call_user_func_array($route['handler'], $params);
                return;
            }
        }

        http_response_code(404);
        if (file_exists(BASE_PATH . '/templates/404.php')) {
            require BASE_PATH . '/templates/404.php';
        } else {
            echo '<h1>404 - Página no encontrada</h1>';
        }
    }

    private function matchRoute(string $routePath, string $uri): array|false
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $uriParts = explode('/', trim($uri, '/'));

        if (count($routeParts) !== count($uriParts)) {
            return false;
        }

        $params = [];
        foreach ($routeParts as $i => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $params[] = urldecode($uriParts[$i]);
            } elseif ($part !== $uriParts[$i]) {
                return false;
            }
        }

        return $params;
    }

    public function url(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \RuntimeException("Ruta no encontrada: {$name}");
        }

        $url = $this->namedRoutes[$name];
        foreach ($params as $key => $value) {
            $url = str_replace("{{$key}}", (string) $value, $url);
        }

        return $url;
    }

    public function currentUrl(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    public function name(string $name): self
    {
        $lastIndex = count($this->routes) - 1;
        if ($lastIndex >= 0) {
            $this->routes[$lastIndex]['name'] = $name;
            $this->namedRoutes[$name] = $this->routes[$lastIndex]['path'];
        }
        return $this;
    }
}
