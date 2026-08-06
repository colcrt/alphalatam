<?php

declare(strict_types=1);

namespace App\Core;

class Cache
{
    private static string $cacheDir = '';
    private static int $defaultTtl = 3600;

    public static function init(string $cacheDir): void
    {
        self::$cacheDir = rtrim($cacheDir, '/\\');
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }

    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = self::get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        self::put($key, $value, $ttl);
        return $value;
    }

    public static function get(string $key): mixed
    {
        $file = self::cacheFile($key);
        if (!file_exists($file)) {
            return null;
        }

        $data = unserialize((string) file_get_contents($file));
        if ($data['expires'] < time()) {
            unlink($file);
            return null;
        }

        return $data['value'];
    }

    public static function put(string $key, mixed $value, int $ttl = null): void
    {
        $ttl = $ttl ?? self::$defaultTtl;
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
        ];
        file_put_contents(self::cacheFile($key), serialize($data));
    }

    public static function forget(string $key): void
    {
        $file = self::cacheFile($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public static function flush(): void
    {
        $files = glob(self::$cacheDir . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }

    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    private static function cacheFile(string $key): string
    {
        return self::$cacheDir . '/' . md5($key) . '.cache';
    }
}
