<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Core\Auth\Auth;
use App\Core\Cache;
use App\Database\Connection;
use App\Domain\PartidoPolitico\DTO\PartidoData;
use App\Exceptions\PublicacionSinFuenteException;
use App\Models\PartidoPolitico;

class PartidoService
{
    private $repositorio;
    private SeoService $seoService;

    public function __construct($repositorio = null, ?SeoService $seoService = null)
    {
        $this->repositorio = $repositorio ?? App::make('App\\Repositories\\Contracts\\PartidoPoliticoRepositoryInterface');
        $this->seoService = $seoService ?? App::make(SeoService::class);
    }

    public function paraAdmin(array $filtros = [])
    {
        return $this->repositorio->paginarParaAdmin($filtros);
    }

    public function obtenerFichaPublica(string $slug): ?PartidoPolitico
    {
        return Cache::remember(
            "partido:ficha:{$slug}",
            21600,
            fn () => $this->repositorio->buscarPorSlugPublicado($slug)
        );
    }

    public function crear(PartidoData $datos): PartidoPolitico
    {
        return Connection::transaction(function () use ($datos) {
            $atributos = $datos->toModelArray();
            $atributos['slug'] = $this->generarSlugUnico($datos->nombre);
            $atributos['created_by'] = Auth::id();
            $atributos['updated_by'] = Auth::id();

            $this->seoService->autocompletar($atributos, $datos->nombre, $datos->descripcion);

            return $this->repositorio->crear($atributos);
        });
    }

    public function actualizar(PartidoPolitico $partido, PartidoData $datos): PartidoPolitico
    {
        return Connection::transaction(function () use ($partido, $datos) {
            $atributos = $datos->toModelArray();
            $atributos['updated_by'] = Auth::id();

            $partido = $this->repositorio->actualizar($partido, $atributos);

            Cache::forget("partido:ficha:{$partido->slug}");

            return $partido;
        });
    }

    public function publicar(PartidoPolitico $partido): PartidoPolitico
    {
        if (count($partido->fuentes()) === 0) {
            throw new PublicacionSinFuenteException(
                'No se puede publicar un Partido Político sin al menos una fuente asociada.'
            );
        }

        $partido->update(['status' => 'publicado', 'updated_by' => Auth::id()]);

        Cache::forget("partido:ficha:{$partido->slug}");

        return $partido;
    }

    public function eliminar(PartidoPolitico $partido): bool
    {
        Cache::forget("partido:ficha:{$partido->slug}");

        return $this->repositorio->eliminar($partido);
    }

    private function generarSlugUnico(string $nombre): string
    {
        $base = str_slug($nombre);
        $slug = $base;
        $i = 1;

        while (PartidoPolitico::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
