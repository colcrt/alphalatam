<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    public static function html(string $html, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

    public static function json(mixed $data, int $status = 200): void
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public static function redirect(string $url, int $status = 302): void
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        http_response_code($status);
        header("Location: {$url}");
        exit;
    }

    public static function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';

        if ($referer !== '' && self::esUrlInterna($referer)) {
            self::redirect($referer);
        }

        self::redirect('/');
    }

    private static function esUrlInterna(string $url): bool
    {
        $url = trim($url);
        $partes = parse_url($url);

        if (!isset($partes['scheme'], $partes['host'])) {
            return false;
        }

        $esquema = strtolower((string) $partes['scheme']);
        if (!in_array($esquema, ['http', 'https'], true)) {
            return false;
        }

        $appUrl = rtrim((string) ($_ENV['APP_URL'] ?? ''), '/');
        $appPartes = parse_url($appUrl);

        if (!isset($appPartes['host'])) {
            return false;
        }

        $mismoHost = strtolower((string) $partes['host']) === strtolower((string) $appPartes['host']);
        $mismoPuerto = ($partes['port'] ?? null) === ($appPartes['port'] ?? null);

        return $mismoHost && $mismoPuerto;
    }

    public static function withSuccess(string $url, string $message): void
    {
        Session::flash('exito', $message);
        self::redirect($url);
    }

    public static function withError(string $url, string $message): void
    {
        Session::flash('error', $message);
        self::redirect($url);
    }

    public static function notFound(): void
    {
        http_response_code(404);
        $viewPath = BASE_PATH . '/templates/404.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo '<h1>404 - Página no encontrada</h1>';
        }
    }

    public static function forbidden(): void
    {
        http_response_code(403);
        echo '<h1>403 - Acceso denegado</h1>';
    }

    public static function unauthorized(): void
    {
        http_response_code(401);
        self::redirect('/login');
    }

    public static function statusCode(int $status): void
    {
        http_response_code($status);
    }
}
