<?php

declare(strict_types=1);

namespace App\Domain\Persona\Enums;

enum EstadoJudicial: string
{
    case SinProceso = 'sin_proceso';
    case Investigado = 'investigado';
    case Imputado = 'imputado';
    case Acusado = 'acusado';
    case Condenado = 'condenado';
    case Absuelto = 'absuelto';
    case Archivado = 'archivado';

    public function etiqueta(): string
    {
        return match ($this) {
            self::SinProceso => 'Sin proceso',
            self::Investigado => 'Investigado',
            self::Imputado => 'Imputado',
            self::Acusado => 'Acusado',
            self::Condenado => 'Condenado',
            self::Absuelto => 'Absuelto',
            self::Archivado => 'Archivado',
        };
    }

    public function colorBadge(): string
    {
        return match ($this) {
            self::SinProceso => 'secondary',
            self::Investigado => 'info',
            self::Imputado, self::Acusado => 'warning',
            self::Condenado => 'danger',
            self::Absuelto => 'success',
            self::Archivado => 'dark',
        };
    }
}
