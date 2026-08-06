<?php

declare(strict_types=1);

namespace App\Observers;

use App\Database\Connection;
use App\Core\Cache;
use App\Core\Auth\Auth;
use App\Models\PartidoPolitico;

class PartidoPoliticoObserver
{
    public static function created(PartidoPolitico $partido): void
    {
        self::log('created', $partido);
    }

    public static function updated(PartidoPolitico $partido): void
    {
        self::log('updated', $partido, $partido->getChanges());

        Cache::forget("partido:ficha:{$partido->slug}");
        Cache::forget('estadisticas:resumen');
    }

    public static function deleted(PartidoPolitico $partido): void
    {
        self::log('deleted', $partido);

        Cache::forget('estadisticas:resumen');
    }

    public static function restored(PartidoPolitico $partido): void
    {
        self::log('restored', $partido);

        Cache::forget('estadisticas:resumen');
    }

    private static function log(string $accion, PartidoPolitico $entidad, ?array $datos = null): void
    {
        $user = Auth::user();
        Connection::table('activity_log')->insert([
            'log_name' => 'default',
            'description' => $accion,
            'subject_type' => PartidoPolitico::class,
            'subject_id' => $entidad->getKey(),
            'causer_type' => $user ? get_class($user) : null,
            'causer_id' => Auth::id(),
            'properties' => json_encode($datos ?? $entidad->toArray(), JSON_UNESCAPED_UNICODE),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
