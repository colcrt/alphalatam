<?php
declare(strict_types=1);
namespace App\Domain\Persona\DTO;

final class PersonaData
{
    public function __construct(
        public readonly string $nombre,
        public readonly ?string $biografia,
        public readonly ?string $cargoActual,
        public readonly ?string $profesion,
        public readonly ?string $fechaNacimiento,
        public readonly string $estadoJudicialActual,
        public readonly string $status,
        public readonly ?string $metaTitle,
        public readonly ?string $metaDescription,
        public readonly ?int $fuenteIdEstadoJudicial = null,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            nombre: $d['nombre'],
            biografia: $d['biografia'] ?? null,
            cargoActual: $d['cargo_actual'] ?? null,
            profesion: $d['profesion'] ?? null,
            fechaNacimiento: $d['fecha_nacimiento'] ?? null,
            estadoJudicialActual: $d['estado_judicial_actual'] ?? 'sin_proceso',
            status: $d['status'] ?? 'borrador',
            metaTitle: $d['meta_title'] ?? null,
            metaDescription: $d['meta_description'] ?? null,
            fuenteIdEstadoJudicial: $d['fuente_id_estado_judicial'] ?? null,
        );
    }

    public function toModelArray(): array
    {
        return [
            'nombre' => $this->nombre, 'biografia' => $this->biografia,
            'cargo_actual' => $this->cargoActual, 'profesion' => $this->profesion,
            'fecha_nacimiento' => $this->fechaNacimiento,
            'estado_judicial_actual' => $this->estadoJudicialActual,
            'status' => $this->status, 'meta_title' => $this->metaTitle,
            'meta_description' => $this->metaDescription,
        ];
    }
}
