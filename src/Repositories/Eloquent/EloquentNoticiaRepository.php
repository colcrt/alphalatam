<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Database\Connection;
use App\Models\Noticia;
use App\Repositories\Contracts\NoticiaRepositoryInterface;

class EloquentNoticiaRepository implements NoticiaRepositoryInterface
{
    public function paginarParaAdmin(array $filtros = [], int $porPagina = 20, int $pagina = 1): array
    {
        $query = Connection::table('noticias')
            ->whereNull('deleted_at');

        if (!empty($filtros['busqueda'])) {
            $busqueda = '%' . $filtros['busqueda'] . '%';
            $query = $query->where('titulo', 'LIKE', $busqueda)
                ->orWhere('resumen', 'LIKE', $busqueda);
        }

        if (!empty($filtros['status'])) {
            $query = $query->where('status', $filtros['status']);
        }

        if (!empty($filtros['fuente'])) {
            $query = $query->where('fuente_nombre', 'LIKE', '%' . $filtros['fuente'] . '%');
        }

        $query = $query->orderByDesc('fecha_publicacion');

        return $query->paginate($porPagina, $pagina);
    }

    public function buscarPorSlugPublicado(string $slug): ?Noticia
    {
        $row = Connection::table('noticias')
            ->where('slug', $slug)
            ->where('status', 'publicado')
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            return null;
        }

        $noticia = Noticia::fromRow($row);
        $this->cargarRelaciones($noticia);

        return $noticia;
    }

    public function crear(array $datos): Noticia
    {
        $datos['slug'] = Noticia::generarSlug($datos['titulo'] ?? '');
        $noticia = Noticia::create($datos);
        return $noticia;
    }

    public function actualizar(Noticia $noticia, array $datos): Noticia
    {
        if (isset($datos['titulo']) && $datos['titulo'] !== $noticia->titulo) {
            $datos['slug'] = Noticia::generarSlug($datos['titulo']);
        }

        $noticia->update($datos);
        return $noticia;
    }

    public function eliminar(Noticia $noticia): bool
    {
        return $noticia->delete();
    }

    public function contar(): int
    {
        return Connection::table('noticias')
            ->whereNull('deleted_at')
            ->count();
    }

    private function cargarRelaciones(Noticia $noticia): void
    {
        $noticia->relaciones = [
            'casos' => $noticia->casos(),
            'personas' => $noticia->personas(),
            'instituciones' => $noticia->instituciones(),
        ];
    }
}
