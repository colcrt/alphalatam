<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Model;

class Etiqueta extends Model
{
    protected string $table = 'etiquetas';

    protected array $fillable = [
        'nombre',
        'slug',
    ];
}
