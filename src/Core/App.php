<?php

declare(strict_types=1);

namespace App\Core;

class App
{
    private static array $bindings = [];
    private static array $instances = [];

    public static function bind(string $key, mixed $resolver): void
    {
        self::$bindings[$key] = $resolver;
    }

    public static function singleton(string $key, mixed $instance): void
    {
        self::$instances[$key] = $instance;
    }

    public static function make(string $key): mixed
    {
        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }

        if (isset(self::$bindings[$key])) {
            $resolver = self::$bindings[$key];
            if (is_callable($resolver) && !is_string($resolver)) {
                $instance = $resolver();
            } elseif (is_string($resolver) && class_exists($resolver)) {
                $instance = new $resolver();
            } elseif (is_object($resolver)) {
                $instance = $resolver;
            } else {
                $instance = $resolver;
            }
            self::$instances[$key] = $instance;
            return $instance;
        }

        if (class_exists($key)) {
            $ref = new \ReflectionClass($key);
            $constructor = $ref->getConstructor();

            if ($constructor === null) {
                $instance = new $key();
            } else {
                $params = $constructor->getParameters();
                $args = [];
                foreach ($params as $param) {
                    $type = $param->getType();
                    if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                        $args[] = self::make($type->getName());
                    } elseif ($param->isDefaultValueAvailable()) {
                        $args[] = $param->getDefaultValue();
                    } else {
                        $args[] = null;
                    }
                }
                $instance = $ref->newInstanceArgs($args);
            }
            self::$instances[$key] = $instance;
            return $instance;
        }

        throw new \RuntimeException("No se pudo resolver: {$key}");
    }

    public static function has(string $key): bool
    {
        return isset(self::$instances[$key]) || isset(self::$bindings[$key]);
    }
}
