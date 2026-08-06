<?php

declare(strict_types=1);

namespace App\Services;

class SeoService
{
    public function autocompletar(array &$atributos, string $tituloBase, ?string $textoBase = null): void
    {
        $atributos['meta_title'] = $atributos['meta_title'] ?: str_limit($tituloBase, 60, '');
        $atributos['meta_description'] = $atributos['meta_description']
            ?: str_limit(strip_tags($textoBase ?? ''), 155);
    }

    public function jsonLdPersona(\App\Models\Persona $persona): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $persona->nombre,
            'jobTitle' => $persona->cargo_actual,
            'description' => $persona->meta_description ?? $persona->biografia ?? '',
            'image' => $persona->foto_path ? asset('uploads/' . $persona->foto_path) : null,
            'url' => route('personas.show', ['slug' => $persona->slug]),
        ];
    }

    public function jsonLdCaso(\App\Models\Caso $caso): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $caso->nombre,
            'description' => $caso->meta_description ?? $caso->resumen ?? '',
            'datePublished' => $caso->fecha_inicio ?: null,
            'url' => route('casos.show', ['slug' => $caso->slug]),
        ];
    }

    public function jsonLdInstitucion(\App\Models\Institucion $institucion): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $institucion->nombre,
            'description' => $institucion->meta_description ?? $institucion->descripcion ?? '',
            'url' => route('instituciones.show', ['slug' => $institucion->slug]),
        ];
    }

    public function jsonLdPartido(\App\Models\PartidoPolitico $partido): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $partido->nombre,
            'description' => $partido->meta_description ?? $partido->descripcion ?? '',
            'url' => route('partidos.show', ['slug' => $partido->slug]),
        ];
    }

    public function jsonLdEmpresa(\App\Models\Empresa $empresa): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $empresa->nombre,
            'description' => $empresa->meta_description ?? $empresa->descripcion ?? '',
            'url' => route('empresas.show', ['slug' => $empresa->slug]),
        ];
    }

    public function jsonLdSentencia(\App\Models\Sentencia $sentencia): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => 'Sentencia ' . $sentencia->numero,
            'description' => $sentencia->meta_description ?? $sentencia->resumen ?? '',
            'datePublished' => $sentencia->fecha ?: null,
            'url' => route('sentencias.show', ['slug' => $sentencia->slug]),
        ];
    }

    public function jsonLdNoticia(\App\Models\Noticia $noticia): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $noticia->titulo,
            'description' => $noticia->meta_description ?? $noticia->resumen ?? '',
            'datePublished' => $noticia->fecha_publicacion ?: null,
            'publisher' => $noticia->medio,
            'url' => route('noticias.show', ['slug' => $noticia->slug]),
        ];
    }

    public function breadcrumbJsonLd(array $items): array
    {
        $list = [];
        $i = 1;
        foreach ($items as $item) {
            $list[] = [
                '@type' => 'ListItem',
                'position' => $i,
                'name' => $item['nombre'],
                'item' => $item['url'],
            ];
            $i++;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $list,
        ];
    }
}
