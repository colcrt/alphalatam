<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Model;

class BlogComentario extends Model
{
    protected string $table = 'blog_comentarios';

    protected array $fillable = [
        'blog_post_id',
        'parent_id',
        'autor_nombre',
        'autor_email',
        'contenido',
        'status',
    ];

    public function post(): ?BlogPost
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id', 'id');
    }

    public function respuestas(): array
    {
        return $this->hasMany(BlogComentario::class, 'parent_id', 'id');
    }
}
