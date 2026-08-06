<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Core\Cache;
use App\Database\Connection;
use App\Domain\Noticia\DTO\NoticiaData;
use App\Models\Noticia;

class NoticiaService
{
    private $repositorio;
    private SeoService $seoService;

    public function __construct($repositorio = null, ?SeoService $seoService = null)
    {
        $this->repositorio = $repositorio ?? App::make('App\\Repositories\\Contracts\\NoticiaRepositoryInterface');
        $this->seoService = $seoService ?? App::make(SeoService::class);
    }

    public function paraAdmin(array $filtros = [])
    {
        return $this->repositorio->paginarParaAdmin($filtros);
    }

    public function obtenerFichaPublica(string $slug): ?Noticia
    {
        return Cache::remember(
            "noticia:ficha:{$slug}",
            21600,
            fn () => $this->repositorio->buscarPorSlugPublicado($slug)
        );
    }

    public function crear(NoticiaData $datos): Noticia
    {
        return Connection::transaction(function () use ($datos) {
            $atributos = $datos->toModelArray();
            $atributos['slug'] = $this->generarSlugUnico($datos->titulo);

            $this->seoService->autocompletar($atributos, $datos->titulo, $datos->resumen);

            return $this->repositorio->crear($atributos);
        });
    }

    public function actualizar(Noticia $noticia, NoticiaData $datos): Noticia
    {
        return Connection::transaction(function () use ($noticia, $datos) {
            $atributos = $datos->toModelArray();

            $noticia = $this->repositorio->actualizar($noticia, $atributos);

            Cache::forget("noticia:ficha:{$noticia->slug}");

            return $noticia;
        });
    }

    public function publicar(Noticia $noticia): Noticia
    {
        $noticia->update(['status' => 'publicado']);

        Cache::forget("noticia:ficha:{$noticia->slug}");

        return $noticia;
    }

    public function eliminar(Noticia $noticia): bool
    {
        Cache::forget("noticia:ficha:{$noticia->slug}");

        return $this->repositorio->eliminar($noticia);
    }

    private function generarSlugUnico(string $titulo): string
    {
        $base = str_slug($titulo);
        $slug = $base;
        $i = 1;

        while (Noticia::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
