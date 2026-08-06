<?php
declare(strict_types=1);
namespace App\Domain\Fuente\DTO;

final class FuenteData
{
    public function __construct(
        public readonly string $tipo,
        public readonly string $titulo,
        public readonly ?string $url,
        public readonly ?string $autor,
        public readonly ?string $fechaPublicacion,
        public readonly ?string $descripcion,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            tipo: $d['tipo'], titulo: $d['titulo'],
            url: $d['url'] ?? null, autor: $d['autor'] ?? null,
            fechaPublicacion: $d['fecha_publicacion'] ?? null,
            descripcion: $d['descripcion'] ?? null,
        );
    }

    public function toModelArray(): array
    {
        return [
            'tipo' => $this->tipo, 'titulo' => $this->titulo,
            'url' => $this->url, 'autor' => $this->autor,
            'fecha_publicacion' => $this->fechaPublicacion,
            'descripcion' => $this->descripcion,
        ];
    }
}
