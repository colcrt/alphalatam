<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Model;

class Noticia extends Model
{
    protected string $table = 'noticias';

    protected bool $softDeletes = true;

    protected array $fillable = [
        'titulo',
        'slug',
        'resumen',
        'url_original',
        'medio',
        'fecha_publicacion',
        'status',
        'meta_title',
        'meta_description',
        'canonical_url',
        'schema_json',
    ];

    protected array $casts = [
        'fecha_publicacion' => 'date',
        'schema_json' => 'array',
    ];

    public function casos(): array
    {
        return $this->belongsToMany(Caso::class, 'caso_noticia', 'noticia_id', 'caso_id');
    }

    public function personas(): array
    {
        return $this->belongsToMany(Persona::class, 'noticia_persona', 'noticia_id', 'persona_id');
    }

    public function instituciones(): array
    {
        return $this->belongsToMany(Institucion::class, 'noticia_institucion', 'noticia_id', 'institucion_id');
    }
}
