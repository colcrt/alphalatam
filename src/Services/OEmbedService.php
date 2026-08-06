<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Cache;

class OEmbedService
{
    private const CACHE_TTL = 86400;

    private const ALLOWED_HOSTS = [
        'youtube.com', 'www.youtube.com', 'youtu.be',
        'vimeo.com', 'player.vimeo.com',
        'twitter.com', 'x.com', 'www.twitter.com', 'www.x.com',
        'tiktok.com', 'www.tiktok.com',
        'instagram.com', 'www.instagram.com',
        'facebook.com', 'www.facebook.com', 'web.facebook.com',
        'open.spotify.com',
        'soundcloud.com', 'www.soundcloud.com',
        'dailymotion.com', 'www.dailymotion.com',
        'reddit.com', 'www.reddit.com',
        'codepen.io',
    ];

    private const TAGS_OEMBED = [
        'div', 'span', 'p', 'br', 'blockquote', 'figure', 'figcaption',
        'a', 'img', 'iframe', 'video', 'source',
        'strong', 'em', 'b', 'i', 'u', 's', 'small', 'sub', 'sup',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'audio', 'pre', 'code',
    ];

    public function resolve(string $url): array
    {
        $url = trim($url);

        if (!$url) {
            return ['success' => false, 'error' => 'La URL está vacía.'];
        }

        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['host'])) {
            return ['success' => false, 'error' => 'URL no válida.'];
        }

        $host = strtolower($parsed['host']);
        if (!$this->isAllowedHost($host)) {
            return ['success' => false, 'error' => 'Plataforma no soportada.'];
        }

        $provider = $this->detectProvider($host, $url);

        // Convert x.com → twitter.com for Twitter/X (oEmbed only works with twitter.com)
        if ($provider === 'twitter') {
            $url = $this->normalizeTwitterUrl($url);
        }

        $cacheKey = 'oembed:v2:' . md5($url);
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        // Try oEmbed first
        $oembedUrl = $this->getOembedEndpoint($provider, $url);
        if ($oembedUrl) {
            $result = $this->fetchOembed($oembedUrl, $provider, $url);
            if ($result['success']) {
                Cache::put($cacheKey, $result, self::CACHE_TTL);
                return $result;
            }
        }

        // Twitter fallback: try syndication API
        if ($provider === 'twitter') {
            $result = $this->fetchTwitterSyndication($url);
            if ($result['success']) {
                Cache::put($cacheKey, $result, self::CACHE_TTL);
                return $result;
            }
        }

        // Generic fallbacks
        $result = $this->getFallbackEmbed($provider, $url);
        if ($result['success']) {
            Cache::put($cacheKey, $result, self::CACHE_TTL);
        }

        return $result;
    }

    private function isAllowedHost(string $host): bool
    {
        foreach (self::ALLOWED_HOSTS as $allowed) {
            if ($host === $allowed || str_ends_with($host, '.' . $allowed)) {
                return true;
            }
        }
        return false;
    }

    private function detectProvider(string $host, string $url): string
    {
        if (str_contains($host, 'youtube.com') || $host === 'youtu.be') {
            return 'youtube';
        }
        if (str_contains($host, 'vimeo.com')) {
            return 'vimeo';
        }
        if (str_contains($host, 'twitter.com') || str_ends_with($host, 'x.com')) {
            return 'twitter';
        }
        if (str_contains($host, 'tiktok.com')) {
            return 'tiktok';
        }
        if (str_contains($host, 'instagram.com')) {
            return 'instagram';
        }
        if (str_contains($host, 'facebook.com')) {
            return 'facebook';
        }
        if (str_contains($host, 'spotify.com')) {
            return 'spotify';
        }
        if (str_contains($host, 'soundcloud.com')) {
            return 'soundcloud';
        }
        if (str_contains($host, 'dailymotion.com')) {
            return 'dailymotion';
        }
        if (str_contains($host, 'reddit.com')) {
            return 'reddit';
        }
        if (str_contains($host, 'codepen.io')) {
            return 'codepen';
        }

        return 'generic';
    }

    private function normalizeTwitterUrl(string $url): string
    {
        return str_replace(
            ['://x.com', '://www.x.com'],
            ['://twitter.com', '://www.twitter.com'],
            $url
        );
    }

    private function getOembedEndpoint(string $provider, string $url): ?string
    {
        return match ($provider) {
            'youtube' => "https://www.youtube.com/oembed?url=" . urlencode($url) . "&format=json",
            'vimeo' => "https://vimeo.com/api/oembed.json?url=" . urlencode($url),
            'twitter' => "https://publish.twitter.com/oembed?url=" . urlencode($url) . "&omit_script=true&dnt=true",
            'soundcloud' => "https://soundcloud.com/oembed?url=" . urlencode($url) . "&format=json",
            'dailymotion' => "https://www.dailymotion.com/api/oembed?url=" . urlencode($url) . "&format=json",
            'codepen' => "https://codepen.io/api/oembed?url=" . urlencode($url) . "&format=json",
            default => null,
        };
    }

    private function fetchOembed(string $oembedUrl, string $provider, string $originalUrl): array
    {
        try {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'header' => "User-Agent: AlphaLatamBot/1.0\r\nAccept: application/json\r\n",
                ],
            ]);

            $response = @file_get_contents($oembedUrl, false, $ctx);
            if ($response === false) {
                return ['success' => false, 'error' => 'No se pudo obtener la información del embed.'];
            }

            $data = json_decode($response, true);
            if (!$data || empty($data['html'])) {
                return ['success' => false, 'error' => 'Respuesta oEmbed vacía.'];
            }

            $html = $data['html'];
            $providerName = $data['provider_name'] ?? ucfirst($provider);
            $type = $data['type'] ?? 'rich';

            $html = $this->sanitizarHtmlOembed($html);

            if ($html === '') {
                return ['success' => false, 'error' => 'El embed no contiene HTML permitido.'];
            }

            $html = $this->wrapHtml($html, $provider, $providerName);

            return [
                'success' => true,
                'html' => $html,
                'provider' => $provider,
                'provider_name' => $providerName,
                'type' => $type,
                'width' => $data['width'] ?? null,
                'height' => $data['height'] ?? null,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'Error al obtener el embed.'];
        }
    }

    private function wrapHtml(string $html, string $provider, string $providerName): string
    {
        $class = "embed-{$provider}";
        return '<div class="embed-wrapper ' . $class . '" data-oembed="' . htmlspecialchars($providerName) . '">' . $html . '</div>';
    }

    /**
     * Sanea el HTML devuelto por un proveedor oEmbed mediante una lista blanca
     * de etiquetas y atributos, evitando la inyección de scripts (XSS).
     */
    private function sanitizarHtmlOembed(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><div class="oembed-raiz">' . $html . '</div>', LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body || !$body->firstChild) {
            return '';
        }

        $raiz = $body->firstChild;
        if (!$raiz instanceof \DOMElement || $raiz->getAttribute('class') !== 'oembed-raiz') {
            return '';
        }

        $this->purificarNodo($raiz);

        $salida = '';
        foreach ($raiz->childNodes as $hijo) {
            $salida .= $dom->saveHTML($hijo);
        }

        return trim($salida);
    }

    private function purificarNodo(\DOMNode $nodo): void
    {
        foreach (iterator_to_array($nodo->childNodes) as $hijo) {
            if ($hijo->nodeType === XML_ELEMENT_NODE) {
                $tag = strtolower($hijo->nodeName);

                if (!in_array($tag, self::TAGS_OEMBED, true)) {
                    $this->purificarNodo($hijo);
                    while ($hijo->firstChild) {
                        $nodo->insertBefore($hijo->firstChild, $hijo);
                    }
                    $nodo->removeChild($hijo);
                    continue;
                }

                $this->limpiarAtributos($hijo, $tag);
                $this->purificarNodo($hijo);
            } elseif ($hijo->nodeType === XML_COMMENT_NODE || $hijo->nodeType === XML_PI_NODE) {
                $nodo->removeChild($hijo);
            }
        }
    }

    private function limpiarAtributos(\DOMElement $el, string $tag): void
    {
        foreach (iterator_to_array($el->attributes) as $attr) {
            $nombre = strtolower($attr->nodeName);
            $valor = $attr->nodeValue;

            if (str_starts_with($nombre, 'on') || in_array($nombre, ['srcdoc', 'formaction', 'xlink:href'], true)) {
                $el->removeAttribute($attr->nodeName);
                continue;
            }

            if (in_array($nombre, ['href', 'src', 'poster'], true)) {
                if (!$this->esUrlSegura($valor)) {
                    $el->removeAttribute($attr->nodeName);
                    continue;
                }
                if ($nombre === 'src' && $tag === 'iframe' && !$this->esHostOembedPermitido($valor)) {
                    $el->removeAttribute($attr->nodeName);
                }
            }
        }
    }

    private function esUrlSegura(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || str_starts_with($url, '#')) {
            return true;
        }
        $partes = parse_url($url);
        if (!isset($partes['scheme'])) {
            return false;
        }
        return in_array(strtolower((string) $partes['scheme']), ['http', 'https'], true);
    }

    private function esHostOembedPermitido(string $url): bool
    {
        $partes = parse_url($url);
        if (!isset($partes['host'])) {
            return false;
        }
        return $this->isAllowedHost(strtolower((string) $partes['host']));
    }

    // --- Twitter Syndication API ---

    private function extractTweetId(string $url): ?string
    {
        if (preg_match('/\/status\/(\d+)/', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    private function generateSyndicationToken(string $tweetId): string
    {
        $num = (float) $tweetId;
        $value = ($num / 1e15) * M_PI;

        $intVal = (int) $value;

        $chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        $base36 = '';
        if ($intVal === 0) {
            $base36 = '0';
        } else {
            while ($intVal > 0) {
                $base36 = $chars[$intVal % 36] . $base36;
                $intVal = (int) ($intVal / 36);
            }
        }

        $base36 = preg_replace('/0+$/', '', $base36);

        return $base36;
    }

    private function fetchTwitterSyndication(string $url): array
    {
        $tweetId = $this->extractTweetId($url);
        if (!$tweetId) {
            return ['success' => false, 'error' => 'No se pudo extraer el ID del tweet.'];
        }

        $token = $this->generateSyndicationToken($tweetId);
        $apiUrl = "https://cdn.syndication.twimg.com/tweet-result?id={$tweetId}&token={$token}&lang=es";

        try {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'header' => "User-Agent: Mozilla/5.0\r\nAccept: application/json\r\n",
                ],
            ]);

            $response = @file_get_contents($apiUrl, false, $ctx);
            if ($response === false) {
                return ['success' => false, 'error' => 'No se pudo obtener el tweet.'];
            }

            $data = json_decode($response, true);
            if (!$data) {
                return ['success' => false, 'error' => 'Respuesta inválida del servidor.'];
            }

            $html = $this->buildTweetCard($data, $url);

            return [
                'success' => true,
                'html' => $html,
                'provider' => 'twitter',
                'provider_name' => 'X / Twitter',
                'type' => 'rich',
                'width' => null,
                'height' => null,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'Error al obtener el tweet.'];
        }
    }

    private function buildTweetCard(array $data, string $url): string
    {
        $author = $data['author'] ?? [];
        $name = htmlspecialchars($author['name'] ?? 'Usuario');
        $screenName = htmlspecialchars($author['screen_name'] ?? '');
        $profileImage = htmlspecialchars($author['profile_image_url'] ?? '');
        $text = $this->formatTweetText($data['text'] ?? '', $data['entities'] ?? []);

        $createdAt = '';
        if (!empty($data['created_at'])) {
            $ts = strtotime($data['created_at']);
            $createdAt = date('d M Y', $ts);
        }

        $mediaHtml = '';

        // Photos
        if (!empty($data['photos'])) {
            $photoCount = count($data['photos']);
            $gridClass = $photoCount === 1 ? 'tweet-photos--single' : ($photoCount === 2 ? 'tweet-photos--double' : 'tweet-photos--grid');
            $mediaHtml .= '<div class="tweet-photos ' . $gridClass . '">';
            foreach ($data['photos'] as $photo) {
                $mediaHtml .= '<img src="' . htmlspecialchars($photo['url']) . '" alt="Tweet media" loading="lazy">';
            }
            $mediaHtml .= '</div>';
        }

        // Video
        if (!empty($data['video'])) {
            $video = $data['video'];
            $videoUrl = $video['url'] ?? '';
            $videoType = $video['content_type'] ?? 'video/mp4';
            if ($videoUrl) {
                $mediaHtml .= '<div class="tweet-video">';
                $mediaHtml .= '<video controls preload="metadata" playsinline>';
                $mediaHtml .= '<source src="' . htmlspecialchars($videoUrl) . '" type="' . htmlspecialchars($videoType) . '">';
                $mediaHtml .= '</video>';
                $mediaHtml .= '</div>';
            }
        }

        // Quote tweet
        $quoteHtml = '';
        if (!empty($data['quote'])) {
            $quote = $data['quote'];
            $quoteAuthor = $quote['author'] ?? [];
            $quoteName = htmlspecialchars($quoteAuthor['name'] ?? '');
            $quoteScreen = htmlspecialchars($quoteAuthor['screen_name'] ?? '');
            $quoteText = htmlspecialchars($quote['text'] ?? '');
            $quoteUrl = $quote['url'] ?? '';

            $quoteHtml = '<div class="tweet-quote">';
            $quoteHtml .= '<a href="' . htmlspecialchars($quoteUrl) . '" target="_blank" rel="noopener noreferrer">';
            $quoteHtml .= '<div class="tweet-quote-header">';
            $quoteHtml .= '<span class="tweet-quote-name">' . $quoteName . '</span>';
            $quoteHtml .= '<span class="tweet-quote-screen">@' . $quoteScreen . '</span>';
            $quoteHtml .= '</div>';
            $quoteHtml .= '<p class="tweet-quote-text">' . $quoteText . '</p>';
            $quoteHtml .= '</a>';
            $quoteHtml .= '</div>';
        }

        $html = '<div class="tweet-card">';
        $html .= '<a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener noreferrer" class="tweet-card-link">';
        $html .= '<div class="tweet-header">';
        if ($profileImage) {
            $html .= '<img src="' . $profileImage . '" alt="' . $name . '" class="tweet-avatar">';
        }
        $html .= '<div class="tweet-author">';
        $html .= '<span class="tweet-name">' . $name . '</span>';
        $html .= '<span class="tweet-screen">@' . $screenName . '</span>';
        $html .= '</div>';
        $html .= '<svg class="tweet-logo" viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>';
        $html .= '</div>';
        $html .= '<p class="tweet-text">' . $text . '</p>';
        $html .= '</a>';
        if ($mediaHtml) {
            $html .= '<div class="tweet-media">' . $mediaHtml . '</div>';
        }
        if ($quoteHtml) {
            $html .= $quoteHtml;
        }
        $html .= '<div class="tweet-footer">';
        if ($createdAt) {
            $html .= '<span class="tweet-date">' . $createdAt . '</span>';
        }
        $html .= '<span class="tweet-via">via X</span>';
        $html .= '</div>';
        $html .= '</div>';

        return '<div class="embed-wrapper embed-twitter" data-oembed="X / Twitter">' . $html . '</div>';
    }

    private function formatTweetText(string $text, array $entities): string
    {
        $text = htmlspecialchars($text);

        // Convert URLs
        if (!empty($entities['urls'])) {
            foreach ($entities['urls'] as $urlEntity) {
                $original = $urlEntity['url'] ?? '';
                $display = $urlEntity['display_url'] ?? $original;
                $expanded = $urlEntity['expanded_url'] ?? $original;
                if ($original && $this->esUrlSegura($expanded)) {
                    $text = str_replace(
                        htmlspecialchars($original),
                        '<a href="' . htmlspecialchars($expanded) . '" target="_blank" rel="noopener">' . htmlspecialchars($display) . '</a>',
                        $text
                    );
                }
            }
        }

        // Convert hashtags
        if (!empty($entities['hashtags'])) {
            foreach ($entities['hashtags'] as $hashtag) {
                $tag = $hashtag['text'] ?? '';
                if ($tag) {
                    $text = str_replace(
                        '#' . htmlspecialchars($tag),
                        '<a href="https://twitter.com/hashtag/' . htmlspecialchars($tag) . '" target="_blank" rel="noopener">#' . htmlspecialchars($tag) . '</a>',
                        $text
                    );
                }
            }
        }

        // Convert mentions
        if (!empty($entities['mentions'])) {
            foreach ($entities['mentions'] as $mention) {
                $handle = $mention['screen_name'] ?? '';
                if ($handle) {
                    $text = str_replace(
                        '@' . htmlspecialchars($handle),
                        '<a href="https://twitter.com/' . htmlspecialchars($handle) . '" target="_blank" rel="noopener">@' . htmlspecialchars($handle) . '</a>',
                        $text
                    );
                }
            }
        }

        // Newlines
        $text = nl2br($text);

        return $text;
    }

    // --- Generic fallbacks ---

    private function getFallbackEmbed(string $provider, string $url): array
    {
        $embeds = [
            'tiktok' => $this->buildTiktokFallback($url),
            'instagram' => $this->buildInstagramFallback($url),
            'facebook' => $this->buildFacebookFallback($url),
            'spotify' => $this->buildSpotifyFallback($url),
            'reddit' => $this->buildRedditFallback($url),
        ];

        if (isset($embeds[$provider])) {
            $html = $embeds[$provider];
            return [
                'success' => true,
                'html' => '<div class="embed-wrapper embed-' . $provider . '" data-oembed="' . htmlspecialchars(ucfirst($provider)) . '">' . $html . '</div>',
                'provider' => $provider,
                'provider_name' => ucfirst($provider),
                'type' => 'rich',
                'width' => null,
                'height' => null,
            ];
        }

        return ['success' => false, 'error' => 'No se pudo resolver el embed para esta plataforma.'];
    }

    private function buildTiktokFallback(string $url): string
    {
        $videoId = $this->extractTiktokId($url);
        if (!$videoId) {
            return '<blockquote class="tiktok-embed" cite="' . htmlspecialchars($url) . '" data-video-id="' . htmlspecialchars($url) . '" style="max-width:605px;min-width:325px;"><section></section></blockquote>';
        }
        return '<blockquote class="tiktok-embed" cite="' . htmlspecialchars($url) . '" data-video-id="' . htmlspecialchars($videoId) . '" style="max-width:605px;min-width:325px;"><section></section></blockquote>';
    }

    private function extractTiktokId(string $url): ?string
    {
        if (preg_match('/\/video\/(\d+)/', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    private function buildInstagramFallback(string $url): string
    {
        return '<blockquote class="instagram-media" data-instgrm-permalink="' . htmlspecialchars($url) . '" style="max-width:540px;min-width:326px;"><div style="padding:16px;"><a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener">Ver en Instagram</a></div></blockquote>';
    }

    private function buildFacebookFallback(string $url): string
    {
        return '<div class="fb-post" data-href="' . htmlspecialchars($url) . '" data-width="500"><blockquote cite="' . htmlspecialchars($url) . '" class="fb-xfbml-parse-ignore"><a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener">Ver en Facebook</a></blockquote></div>';
    }

    private function buildSpotifyFallback(string $url): string
    {
        $spotifyUri = $this->urlToSpotifyUri($url);
        if ($spotifyUri) {
            return '<iframe src="https://open.spotify.com/embed/' . htmlspecialchars($spotifyUri) . '" width="100%" height="352" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy" style="border-radius:12px;"></iframe>';
        }
        return '<iframe src="https://open.spotify.com/embed?src=' . urlencode($url) . '" width="100%" height="352" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy" style="border-radius:12px;"></iframe>';
    }

    private function urlToSpotifyUri(string $url): ?string
    {
        if (preg_match('/spotify\.com\/(track|album|playlist|show|episode)\/([a-zA-Z0-9]+)/', $url, $m)) {
            return $m[1] . '/' . $m[2];
        }
        return null;
    }

    private function buildRedditFallback(string $url): string
    {
        $cleanUrl = preg_replace('/\?.*$/', '', $url);
        return '<iframe src="' . htmlspecialchars($cleanUrl) . '" width="100%" height="316" frameBorder="0" scrolling="no" loading="lazy" style="border-radius:12px;max-width:640px;" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"></iframe>';
    }
}
