<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Model;

class FuenteReferenciable extends Model
{
    protected string $table = 'fuente_referenciable';

    protected bool $timestamps = false;

    protected array $fillable = [
        'fuente_id',
        'referenciable_id',
        'referenciable_type',
    ];

    public function fuente(): ?Fuente
    {
        return $this->belongsTo(Fuente::class, 'fuente_id', 'id');
    }

    public function referenciable(): ?Model
    {
        if (!isset($this->attributes['referenciable_type'], $this->attributes['referenciable_id'])) {
            return null;
        }
        $class = $this->attributes['referenciable_type'];
        return $class::find($this->attributes['referenciable_id']);
    }
}
