<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Model;

class BlogPost extends Model
{
    protected string $table = 'blog_posts';

    protected bool $softDeletes = true;

    protected array $fillable = [
        'titulo',
        'slug',
        'extracto',
        'contenido',
        'tipo',
        'categoria_id',
        'autor_id',
        'imagen_destacada_path',
        'media_destacada_path',
        'media_destacada_tipo',
        'card_embed_url',
        'card_embed_html',
        'status',
        'destacado',
        'interes',
        'published_at',
        'meta_title',
        'meta_description',
        'canonical_url',
        'og_image_path',
        'schema_json',
        'fuentes',
        'delete_requested_at',
        'delete_requested_by',
    ];

    protected array $casts = [
        'published_at' => 'datetime',
        'schema_json' => 'array',
        'destacado' => 'boolean',
        'interes' => 'boolean',
    ];

    public function categoria(): ?BlogCategoria
    {
        return $this->belongsTo(BlogCategoria::class, 'categoria_id', 'id');
    }

    public function autor(): ?User
    {
        return $this->belongsTo(User::class, 'autor_id', 'id');
    }

    public function versiones(): array
    {
        return $this->hasMany(BlogPostVersion::class, 'blog_post_id', 'id');
    }

    public function comentarios(): array
    {
        return $this->hasMany(BlogComentario::class, 'blog_post_id', 'id');
    }

    public function etiquetas(): array
    {
        return $this->morphToMany(Etiqueta::class, 'etiquetables', 'etiquetable_type', 'etiquetable_id');
    }

    public function tieneSolicitudBorrado(): bool
    {
        return $this->delete_requested_at !== null;
    }

    public static function publicados(): \App\Database\QueryBuilder
    {
        return static::query()
            ->where('status', 'publicado')
            ->where('published_at', '<=', date('Y-m-d H:i:s'));
    }

    public static function tiposDisponibles(): array
    {
        return [
            'noticia' => 'Noticia',
            'opinion' => 'Artículo de Opinión',
            'investigacion' => 'Investigación',
        ];
    }
}
