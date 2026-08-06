<?php

declare(strict_types=1);

namespace App\Domain\Institucion\Enums;

enum TipoInstitucion: string
{
    case Fiscalia = 'fiscalia';
    case Contraloria = 'contraloria';
    case Procuraduria = 'procuraduria';
    case CorteSuprema = 'corte_suprema';
    case ConsejoEstado = 'consejo_estado';
    case Jep = 'jep';
    case Otra = 'otra';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Fiscalia => 'Fiscalía',
            self::Contraloria => 'Contraloría',
            self::Procuraduria => 'Procuraduría',
            self::CorteSuprema => 'Corte Suprema',
            self::ConsejoEstado => 'Consejo de Estado',
            self::Jep => 'JEP',
            self::Otra => 'Otra',
        };
    }

    public function colorBadge(): string
    {
        return match ($this) {
            self::Fiscalia => 'danger',
            self::Contraloria => 'warning',
            self::Procuraduria => 'info',
            self::CorteSuprema => 'primary',
            self::ConsejoEstado => 'secondary',
            self::Jep => 'success',
            self::Otra => 'dark',
        };
    }
}
