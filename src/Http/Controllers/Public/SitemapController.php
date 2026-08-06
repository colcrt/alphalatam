<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Database\Connection;

class SitemapController
{
    public function index(): void
    {
        ini_set('default_mimetype', 'application/xml');
        try {
            $baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');

            $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

            // Home page
            $url = $xml->addChild('url');
            $url->addChild('loc', $baseUrl . '/');
            $url->addChild('lastmod', date('Y-m-d'));
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', '1.0');

            $modules = [
                'blog' => ['table' => 'blog_posts', 'route' => 'blog/post', 'priority' => '0.8', 'freq' => 'weekly'],
                'personas' => ['table' => 'personas', 'route' => 'personas', 'priority' => '0.6', 'freq' => 'monthly'],
                'casos' => ['table' => 'casos', 'route' => 'casos', 'priority' => '0.6', 'freq' => 'monthly'],
                'instituciones' => ['table' => 'instituciones', 'route' => 'instituciones', 'priority' => '0.6', 'freq' => 'monthly'],
                'partidos' => ['table' => 'partidos_politicos', 'route' => 'partidos', 'priority' => '0.6', 'freq' => 'monthly'],
                'empresas' => ['table' => 'empresas', 'route' => 'empresas', 'priority' => '0.6', 'freq' => 'monthly'],
                'sentencias' => ['table' => 'sentencias', 'route' => 'sentencias', 'priority' => '0.6', 'freq' => 'monthly'],
                'noticias' => ['table' => 'noticias', 'route' => 'noticias', 'priority' => '0.7', 'freq' => 'weekly'],
            ];

            foreach ($modules as $module) {
                try {
                    $items = Connection::table($module['table'])
                        ->where('status', 'publicado')
                        ->whereNull('deleted_at')
                        ->select('slug', 'updated_at')
                        ->orderByDesc('updated_at')
                        ->get();

                    foreach ($items as $item) {
                        $lastmod = $item->updated_at ? date('Y-m-d', strtotime($item->updated_at)) : date('Y-m-d');
                        $url = $xml->addChild('url');
                        $loc = $url->addChild('loc');
                        $loc[0] = $baseUrl . '/' . $module['route'] . '/' . $item->slug;
                        $url->addChild('lastmod', $lastmod);
                        $url->addChild('changefreq', $module['freq']);
                        $url->addChild('priority', $module['priority']);
                    }
                } catch (\Throwable $e) {
                    continue;
                }
            }

            $xml->asXML(BASE_PATH . '/sitemap.xml');

            ob_clean();
            http_response_code(200);
            header('Content-Type: application/xml; charset=utf-8');
            header('X-Robots-Tag: noindex');
            echo $xml->asXML();
        } catch (\Throwable $e) {
            ob_clean();
            http_response_code(200);
            header('Content-Type: application/xml; charset=utf-8');
            echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>';
        }
    }
}
