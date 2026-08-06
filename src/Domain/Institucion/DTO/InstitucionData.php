<?php
declare(strict_types=1);
namespace App\Domain\Institucion\DTO;

final class InstitucionData
{
    public function __construct(
        public readonly string $nombre,
        public readonly string $tipo,
        public readonly ?string $descripcion,
        public readonly string $status,
        public readonly ?string $metaTitle,
        public readonly ?string $metaDescription,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            nombre: $d['nombre'], tipo: $d['tipo'] ?? 'otra',
            descripcion: $d['descripcion'] ?? null, status: $d['status'] ?? 'borrador',
            metaTitle: $d['meta_title'] ?? null, metaDescription: $d['meta_description'] ?? null,
        );
    }

    public function toModelArray(): array
    {
        return [
            'nombre' => $this->nombre, 'tipo' => $this->tipo,
            'descripcion' => $this->descripcion, 'status' => $this->status,
            'meta_title' => $this->metaTitle, 'meta_description' => $this->metaDescription,
        ];
    }
}
