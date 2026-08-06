<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\BlogPost;

interface BlogRepositoryInterface
{
    public function paginarParaAdmin(array $filtros = [], int $porPagina = 20, int $pagina = 1): array;
    public function buscarPorSlugPublicado(string $slug): ?BlogPost;
    public function crear(array $datos): BlogPost;
    public function actualizar(BlogPost $blog, array $datos): BlogPost;
    public function eliminar(BlogPost $blog): bool;
    public function contar(): int;
}
