<?php
declare(strict_types=1);
namespace App\Domain\Caso\DTO;

final class CasoData
{
    public function __construct(
        public readonly string $nombre,
        public readonly ?string $descripcion,
        public readonly ?string $resumen,
        public readonly ?float $monto,
        public readonly string $estado,
        public readonly ?string $fechaInicio,
        public readonly ?string $fechaCierre,
        public readonly string $status,
        public readonly ?string $metaTitle,
        public readonly ?string $metaDescription,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            nombre: $d['nombre'], descripcion: $d['descripcion'] ?? null,
            resumen: $d['resumen'] ?? null, monto: $d['monto'] ?? null,
            estado: $d['estado'] ?? 'abierto', fechaInicio: $d['fecha_inicio'] ?? null,
            fechaCierre: $d['fecha_cierre'] ?? null, status: $d['status'] ?? 'borrador',
            metaTitle: $d['meta_title'] ?? null, metaDescription: $d['meta_description'] ?? null,
        );
    }

    public function toModelArray(): array
    {
        return [
            'nombre' => $this->nombre, 'descripcion' => $this->descripcion,
            'resumen' => $this->resumen, 'monto' => $this->monto,
            'estado' => $this->estado, 'fecha_inicio' => $this->fechaInicio,
            'fecha_cierre' => $this->fechaCierre, 'status' => $this->status,
            'meta_title' => $this->metaTitle, 'meta_description' => $this->metaDescription,
        ];
    }
}
