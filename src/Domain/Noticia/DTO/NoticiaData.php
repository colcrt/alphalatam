<?php
declare(strict_types=1);
namespace App\Domain\Noticia\DTO;

final class NoticiaData
{
    public function __construct(
        public readonly string $titulo,
        public readonly ?string $resumen,
        public readonly ?string $urlOriginal,
        public readonly ?string $medio,
        public readonly ?string $fechaPublicacion,
        public readonly string $status,
        public readonly ?string $metaTitle,
        public readonly ?string $metaDescription,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            titulo: $d['titulo'], resumen: $d['resumen'] ?? null,
            urlOriginal: $d['url_original'] ?? null, medio: $d['medio'] ?? null,
            fechaPublicacion: $d['fecha_publicacion'] ?? null,
            status: $d['status'] ?? 'borrador',
            metaTitle: $d['meta_title'] ?? null, metaDescription: $d['meta_description'] ?? null,
        );
    }

    public function toModelArray(): array
    {
        return [
            'titulo' => $this->titulo, 'resumen' => $this->resumen,
            'url_original' => $this->urlOriginal, 'medio' => $this->medio,
            'fecha_publicacion' => $this->fechaPublicacion,
            'status' => $this->status, 'meta_title' => $this->metaTitle,
            'meta_description' => $this->metaDescription,
        ];
    }
}
