<?php

declare(strict_types=1);

function esc(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function e(?string $value): string
{
    return esc($value);
}

function str_slug(string $text): string
{
    $text = mb_strtolower(trim($text), 'UTF-8');
    $text = str_replace(
        ['á','é','í','ó','ú','ñ','ü','ä','ë','ï','ö','â','ê','î','ô','û','à','è','ì','ò','ù'],
        ['a','e','i','o','u','n','u','a','e','i','o','a','e','i','o','u','a','e','i','o','u'],
        $text
    );
    $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

function str_limit(string $text, int $limit = 100, string $suffix = '...'): string
{
    if (mb_strlen($text) <= $limit) return $text;
    return mb_substr($text, 0, $limit) . $suffix;
}

function str_plural(string $word, int $count = 2): string
{
    return $count === 1 ? $word : $word . 's';
}

function date_format_es(?string $date, string $format = 'd/m/Y'): string
{
    if (!$date) return '-';
    $ts = strtotime($date);
    if (!$ts) return '-';
    return date($format, $ts);
}

function money_format_es(?float $amount): string
{
    if ($amount === null) return '-';
    return '$ ' . number_format($amount, 0, ',', '.');
}

function asset(string $path): string
{
    $url = $_ENV['APP_URL'] ?? '';
    return rtrim($url, '/') . '/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    $base = $_ENV['APP_URL'] ?? '';
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function url_raw(string $path = ''): string
{
    $base = $_ENV['APP_URL'] ?? '';
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function route(string $name, array $params = []): string
{
    $router = \App\Core\App::make(\App\Core\Router::class);
    return $router->url($name, $params);
}

function old(string $key, mixed $default = ''): mixed
{
    return \App\Core\Session::get("_old_{$key}", $default);
}

function flash(string $key): ?string
{
    return \App\Core\Session::getFlash($key);
}

function csrf_field(): string
{
    return \App\Core\Auth\Csrf::field();
}

function csrf_token(): string
{
    return \App\Core\Auth\Csrf::token();
}

function dd(mixed ...$vars): never
{
    foreach ($vars as $var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
    exit;
}

function storage_path(string $path = ''): string
{
    $base = dirname(__DIR__, 2) . '/storage/app';
    return $path ? rtrim($base, '/') . '/' . ltrim($path, '/') : $base;
}

function json_ld(array $data): string
{
    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

function es_embed_video_tarjeta(string $embedHtml): bool
{
    foreach (['embed-youtube', 'embed-vimeo', 'embed-dailymotion', 'embed-spotify', 'embed-soundcloud'] as $clase) {
        if (str_contains($embedHtml, $clase)) {
            return true;
        }
    }
    return false;
}

/**
 * Media de la tarjeta de un artículo. Precedencia:
 *   1. Embed oEmbed de video (card_embed_html)  2. Media animada (GIF/MP4/WebM)  3. Imagen estática.
 * Devuelve '' si no hay nada que mostrar.
 */
function render_card_media(array|object $post, string $contenedorClases = '', string $mediaClases = 'w-full h-full object-cover'): string
{
    $obtener = static fn(string $campo): string => (string) (is_object($post) ? ($post->$campo ?? '') : ($post[$campo] ?? ''));

    $titulo = esc($obtener('titulo'));
    $imagen = $obtener('imagen_destacada_path');
    $mediaPath = $obtener('media_destacada_path');
    $mediaTipo = $obtener('media_destacada_tipo');
    $embedHtml = $obtener('card_embed_html');

    if ($embedHtml !== '' && es_embed_video_tarjeta($embedHtml)) {
        $clasesContenedor = $contenedorClases !== '' ? $contenedorClases : $mediaClases;
        if (!str_contains($clasesContenedor, 'overflow')) {
            $clasesContenedor .= ' overflow-hidden';
        }
        return '<div class="media-card ' . esc($clasesContenedor) . '">' . $embedHtml . '</div>';
    }

    if ($mediaPath !== '' && $mediaTipo === 'gif') {
        return '<img src="' . esc(asset('uploads/' . $mediaPath)) . '" alt="' . $titulo . '" loading="lazy" decoding="async" class="' . esc($mediaClases) . '">';
    }

    if ($mediaPath !== '' && $mediaTipo === 'video') {
        $poster = $imagen !== '' ? ' poster="' . esc(asset('uploads/' . $imagen)) . '"' : '';
        return '<video autoplay muted loop playsinline preload="auto"' . $poster . ' class="' . esc($mediaClases) . '"><source src="' . esc(asset('uploads/' . $mediaPath)) . '"></video>';
    }

    if ($imagen !== '') {
        return '<img src="' . esc(asset('uploads/' . $imagen)) . '" alt="' . $titulo . '" loading="lazy" decoding="async" class="' . esc($mediaClases) . '">';
    }

    return '';
}
