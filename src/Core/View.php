<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    private static string $templatesPath = '';

    public static function init(string $templatesPath): void
    {
        self::$templatesPath = rtrim($templatesPath, '/\\');
    }

    public static function render(string $name, array $data = []): void
    {
        $filePath = self::$templatesPath . '/' . str_replace('.', '/', $name) . '.php';

        if (!file_exists($filePath)) {
            throw new \RuntimeException("Vista no encontrada: {$name} ({$filePath})");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $filePath;
        $content = ob_get_clean();

        echo $content;
    }

    public static function renderLayout(string $layout, string $content, array $data = []): void
    {
        $layoutPath = self::$templatesPath . '/layouts/' . $layout . '.php';

        if (!file_exists($layoutPath)) {
            throw new \RuntimeException("Layout no encontrado: {$layout}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $layoutPath;
        echo ob_get_clean();
    }

    public static function partial(string $name, array $data = []): void
    {
        $filePath = self::$templatesPath . '/partials/' . str_replace('.', '/', $name) . '.php';

        if (!file_exists($filePath)) {
            throw new \RuntimeException("Partial no encontrado: {$name}");
        }

        extract($data, EXTR_SKIP);
        require $filePath;
    }

    public static function page(string $title, string $content, array $data = []): void
    {
        $data['pageTitle'] = $title;
        $data['content'] = $content;
        self::renderLayout('public', $content, $data);
    }
}
