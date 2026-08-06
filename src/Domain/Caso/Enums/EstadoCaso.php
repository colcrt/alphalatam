<?php

declare(strict_types=1);

namespace App\Domain\Caso\Enums;

enum EstadoCaso: string
{
    case Abierto = 'abierto';
    case EnInvestigacion = 'en_investigacion';
    case ConSentencia = 'con_sentencia';
    case Archivado = 'archivado';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Abierto => 'Abierto',
            self::EnInvestigacion => 'En investigación',
            self::ConSentencia => 'Con sentencia',
            self::Archivado => 'Archivado',
        };
    }

    public function colorBadge(): string
    {
        return match ($this) {
            self::Abierto => 'info',
            self::EnInvestigacion => 'warning',
            self::ConSentencia => 'success',
            self::Archivado => 'secondary',
        };
    }
}
