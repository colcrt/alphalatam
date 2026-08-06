<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Model;

class PartidoPolitico extends Model
{
    protected string $table = 'partidos_politicos';

    protected bool $softDeletes = true;

    protected array $fillable = [
        'nombre',
        'slug',
        'logo_path',
        'historia',
        'descripcion',
        'status',
        'meta_title',
        'meta_description',
        'canonical_url',
        'schema_json',
        'created_by',
        'updated_by',
    ];

    protected array $casts = [
        'schema_json' => 'array',
    ];

    public function personas(): array
    {
        return $this->belongsToMany(Persona::class, 'persona_partido', 'partido_id', 'persona_id');
    }

    public function fuentes(): array
    {
        return $this->morphToMany(Fuente::class, 'fuente_referenciable', 'referenciable_type', 'referenciable_id');
    }
}
