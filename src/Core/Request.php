<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    private array $query;
    private array $body;
    private array $server;
    private array $files;
    private array $cookies;

    private function __construct()
    {
        $this->query = $_GET;
        $this->body = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;
        $this->cookies = $_COOKIE;

        if (str_starts_with($this->server['CONTENT_TYPE'] ?? '', 'application/json')) {
            $this->body = json_decode(file_get_contents('php://input'), true) ?? [];
        }
    }

    public static function fromGlobals(): self
    {
        return new self();
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        return $path === false ? '/' : (rtrim($path, '/') ?: '/');
    }

    public function fullUrl(): string
    {
        $scheme = $this->isSecure() ? 'https' : 'http';
        $host = $this->server['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . ($this->server['REQUEST_URI'] ?? '/');
    }

    public function isSecure(): bool
    {
        return (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off')
            || ($this->server['SERVER_PORT'] ?? 80) == 443
            || ($this->server['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function only(array $keys): array
    {
        $all = $this->all();
        return array_filter($all, fn($v, $k) => in_array($k, $keys), ARRAY_FILTER_USE_BOTH);
    }

    public function except(array $keys): array
    {
        $all = $this->all();
        return array_filter($all, fn($v, $k) => !in_array($k, $keys), ARRAY_FILTER_USE_BOTH);
    }

    public function has(string $key): bool
    {
        return isset($this->body[$key]) || isset($this->query[$key]);
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function header(string $name, ?string $default = null): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$key] ?? $default;
    }

    public function ip(): string
    {
        $forwarded = $this->server['HTTP_X_FORWARDED_FOR'] ?? null;
        if ($forwarded) {
            $ips = array_map('trim', explode(',', $forwarded));
            return $ips[0] ?: '0.0.0.0';
        }
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function expectsJson(): bool
    {
        $accept = $this->header('Accept', '');
        return str_contains($accept, 'application/json')
            || $this->input('_format') === 'json';
    }

    public function json(): ?array
    {
        $content = file_get_contents('php://input');
        return json_decode($content, true);
    }
}
