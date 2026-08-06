<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Sanitiza HTML mediante lista blanca de etiquetas y atributos.
 * Previene XSS almacenado en contenido generado por editores (Quill,
 * embeds oEmbed, etc.) sin eliminar el HTML rico que el CSS .post-content
 * estiliza.
 */
class HtmlSanitizer
{
    private const TAGS_PERMITIDOS = [
        'p', 'br', 'strong', 'em', 'b', 'i', 'u', 's', 'mark', 'small', 'sub', 'sup',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'a', 'img', 'figure', 'figcaption', 'blockquote',
        'ul', 'ol', 'li', 'hr', 'pre', 'code',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
        'div', 'span', 'iframe', 'source', 'video', 'time',
    ];

    private const ATRIBUTOS_PERMITIDOS = [
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
        'iframe' => ['src', 'width', 'height', 'allowfullscreen', 'allow', 'loading', 'frameborder', 'title'],
        'video' => ['src', 'controls', 'poster', 'preload'],
        'source' => ['src', 'type'],
        'blockquote' => ['cite', 'class'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
        'ol' => ['start'],
        'time' => ['datetime'],
        'div' => ['class', 'data-oembed', 'data-video-id', 'data-instgrm-permalink', 'data-href', 'align'],
        'span' => ['class'],
    ];

    private const HOSTS_IFRAME_PERMITIDOS = [
        'youtube.com', 'www.youtube.com', 'youtube-nocookie.com', 'm.youtube.com',
        'player.vimeo.com', 'vimeo.com',
        'open.spotify.com', 'w.soundcloud.com', 'soundcloud.com',
        'platform.twitter.com', 'publish.twitter.com',
        'www.tiktok.com', 'www.instagram.com', 'facebook.com', 'www.facebook.com',
        'reddit.com', 'www.reddit.com', 'embed.reddit.com',
        'twitch.tv', 'www.twitch.tv', 'player.twitch.tv',
        'giphy.com', 'media.giphy.com',
    ];

    private const ATRIBUTOS_BLOQUEADOS = ['on', 'style', 'srcdoc', 'formaction', 'xlink:href'];

    public function sanitizar(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><div class="sanitizer-raiz">' . $html . '</div>', LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body || !$body->firstChild) {
            return '';
        }

        $raiz = $body->firstChild;
        if (!$raiz instanceof \DOMElement || $raiz->getAttribute('class') !== 'sanitizer-raiz') {
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

                if (!in_array($tag, self::TAGS_PERMITIDOS, true)) {
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
        $permitidos = self::ATRIBUTOS_PERMITIDOS[$tag] ?? [];

        foreach (iterator_to_array($el->attributes) as $attr) {
            $nombre = strtolower($attr->nodeName);
            $valor = $attr->nodeValue;

            if ($this->esAtributoBloqueado($nombre)) {
                $el->removeAttribute($attr->nodeName);
                continue;
            }

            if (!in_array($nombre, $permitidos, true)) {
                $el->removeAttribute($attr->nodeName);
                continue;
            }

            if ($nombre === 'class') {
                if (!$this->esClaseSegura($valor)) {
                    $el->removeAttribute($attr->nodeName);
                }
                continue;
            }

            if (in_array($nombre, ['href', 'src', 'poster'], true)) {
                if (!$this->esUrlSegura($valor)) {
                    $el->removeAttribute($attr->nodeName);
                    continue;
                }
                if ($nombre === 'src' && $tag === 'iframe' && !$this->esHostIframePermitido($valor)) {
                    $el->removeAttribute($attr->nodeName);
                }
                continue;
            }

            if ($nombre === 'target' && $valor !== '_blank') {
                $el->removeAttribute($attr->nodeName);
                continue;
            }

            if (in_array($nombre, ['width', 'height', 'colspan', 'rowspan'], true)) {
                if (!preg_match('/^\d+$/', trim($valor))) {
                    $el->removeAttribute($attr->nodeName);
                }
            }
        }

        if ($tag === 'a' && $el->hasAttribute('target')) {
            if (!$el->hasAttribute('rel')) {
                $el->setAttribute('rel', 'noopener nofollow');
            }
        }
    }

    private function esAtributoBloqueado(string $nombre): bool
    {
        foreach (self::ATRIBUTOS_BLOQUEADOS as $bloqueado) {
            if ($bloqueado === 'on') {
                if (str_starts_with($nombre, 'on')) {
                    return true;
                }
                continue;
            }
            if ($nombre === $bloqueado) {
                return true;
            }
        }
        return false;
    }

    private function esClaseSegura(string $clase): bool
    {
        if ($clase === '') {
            return false;
        }
        return preg_match('/^[a-zA-Z0-9_\-\s:]+$/', $clase) === 1;
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

    private function esHostIframePermitido(string $url): bool
    {
        $partes = parse_url($url);
        if (!isset($partes['host'])) {
            return false;
        }
        $host = strtolower((string) $partes['host']);
        foreach (self::HOSTS_IFRAME_PERMITIDOS as $permitido) {
            if ($host === $permitido || str_ends_with($host, '.' . $permitido)) {
                return true;
            }
        }
        return false;
    }
}
