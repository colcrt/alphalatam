<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;

class ConfiguracionService
{
    public function obtener(string $clave, mixed $default = null): mixed
    {
        $fila = Connection::table('configuraciones')
            ->where('clave', $clave)
            ->first();

        if (!$fila) {
            return $default;
        }

        $valor = $fila->valor;

        if (is_string($valor)) {
            $decoded = json_decode($valor, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $valor = $decoded;
            }
        }

        return $valor ?? $default;
    }

    public function guardar(string $clave, mixed $valor, string $grupo = 'general'): void
    {
        $jsonValor = json_encode($valor, JSON_UNESCAPED_UNICODE);

        $existe = Connection::table('configuraciones')
            ->where('clave', $clave)
            ->first();

        if ($existe) {
            Connection::table('configuraciones')
                ->where('clave', $clave)
                ->update([
                    'valor' => $jsonValor,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        } else {
            Connection::table('configuraciones')->insert([
                'clave' => $clave,
                'valor' => $jsonValor,
                'grupo' => $grupo,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function registroHabilitado(): bool
    {
        return (bool) $this->obtener('registro_habilitado', true);
    }
}
