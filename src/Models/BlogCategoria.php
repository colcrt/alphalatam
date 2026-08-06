<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Model;

class BlogCategoria extends Model
{
    protected string $table = 'blog_categorias';

    protected array $fillable = [
        'nombre',
        'slug',
        'descripcion',
    ];

    public function posts(): array
    {
        return $this->hasMany(BlogPost::class, 'categoria_id', 'id');
    }
}
