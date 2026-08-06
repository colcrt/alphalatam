<?php
declare(strict_types=1);
namespace App\Domain\Contrato\DTO;

final class ContratoData
{
    public function __construct(
        public readonly int $empresaId,
        public readonly string $numeroContrato,
        public readonly ?string $objeto,
        public readonly ?float $monto,
        public readonly ?string $fechaFirma,
        public readonly ?string $fechaFinalizacion,
        public readonly string $status,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            empresaId: (int) ($d['empresa_id'] ?? 0), numeroContrato: $d['numero_contrato'],
            objeto: $d['objeto'] ?? null, monto: $d['monto'] ?? null,
            fechaFirma: $d['fecha_firma'] ?? null,
            fechaFinalizacion: $d['fecha_finalizacion'] ?? null,
            status: $d['status'] ?? 'borrador',
        );
    }

    public function toModelArray(): array
    {
        return [
            'empresa_id' => $this->empresaId, 'numero_contrato' => $this->numeroContrato,
            'objeto' => $this->objeto, 'monto' => $this->monto,
            'fecha_firma' => $this->fechaFirma,
            'fecha_finalizacion' => $this->fechaFinalizacion,
            'status' => $this->status,
        ];
    }
}
