<?php

declare(strict_types=1);

namespace App\Domain\Fuente\Enums;

enum TipoFuente: string
{
    case DocumentoOficial = 'documento_oficial';
    case Sentencia = 'sentencia';
    case Noticia = 'noticia';
    case Investigacion = 'investigacion';
    case Video = 'video';
    case Pdf = 'pdf';
    case Doi = 'doi';
    case Repositorio = 'repositorio';

    public function etiqueta(): string
    {
        return match ($this) {
            self::DocumentoOficial => 'Documento Oficial',
            self::Sentencia => 'Sentencia',
            self::Noticia => 'Noticia',
            self::Investigacion => 'Investigación',
            self::Video => 'Video',
            self::Pdf => 'PDF',
            self::Doi => 'DOI',
            self::Repositorio => 'Repositorio',
        };
    }

    public function colorBadge(): string
    {
        return match ($this) {
            self::DocumentoOficial => 'primary',
            self::Sentencia => 'danger',
            self::Noticia => 'warning',
            self::Investigacion => 'info',
            self::Video => 'secondary',
            self::Pdf => 'dark',
            self::Doi => 'success',
            self::Repositorio => 'primary',
        };
    }
}
