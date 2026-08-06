<?php
declare(strict_types=1);
namespace App\Domain\Sentencia\DTO;

final class SentenciaData
{
    public function __construct(
        public readonly int $casoId,
        public readonly string $numero,
        public readonly ?string $fecha,
        public readonly ?string $tribunal,
        public readonly ?string $resumen,
        public readonly string $status,
        public readonly ?string $metaTitle,
        public readonly ?string $metaDescription,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            casoId: (int) ($d['caso_id'] ?? 0), numero: $d['numero'],
            fecha: $d['fecha'] ?? null, tribunal: $d['tribunal'] ?? null,
            resumen: $d['resumen'] ?? null, status: $d['status'] ?? 'borrador',
            metaTitle: $d['meta_title'] ?? null, metaDescription: $d['meta_description'] ?? null,
        );
    }

    public function toModelArray(): array
    {
        return [
            'caso_id' => $this->casoId, 'numero' => $this->numero,
            'fecha' => $this->fecha, 'tribunal' => $this->tribunal,
            'resumen' => $this->resumen, 'status' => $this->status,
            'meta_title' => $this->metaTitle, 'meta_description' => $this->metaDescription,
        ];
    }
}
