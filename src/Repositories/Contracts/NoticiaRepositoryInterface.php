<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Noticia;

interface NoticiaRepositoryInterface
{
    public function paginarParaAdmin(array $filtros = [], int $porPagina = 20, int $pagina = 1): array;
    public function buscarPorSlugPublicado(string $slug): ?Noticia;
    public function crear(array $datos): Noticia;
    public function actualizar(Noticia $noticia, array $datos): Noticia;
    public function eliminar(Noticia $noticia): bool;
    public function contar(): int;
}
