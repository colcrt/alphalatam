<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Model;

class Documento extends Model
{
    protected string $table = 'documentos';

    protected bool $softDeletes = true;

    protected array $fillable = [
        'documentable_id',
        'documentable_type',
        'nombre',
        'tipo',
        'path',
        'tamano',
        'mime_type',
        'version',
        'uploaded_by',
    ];

    public function documentable(): ?Model
    {
        if (!isset($this->attributes['documentable_type'], $this->attributes['documentable_id'])) {
            return null;
        }
        $class = $this->attributes['documentable_type'];
        return $class::find($this->attributes['documentable_id']);
    }

    public function subidoPor(): ?User
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'id');
    }
}
