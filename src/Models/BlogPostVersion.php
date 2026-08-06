<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Model;

class BlogPostVersion extends Model
{
    protected string $table = 'blog_post_versiones';

    protected array $fillable = [
        'blog_post_id',
        'contenido',
        'editado_por',
    ];

    public function post(): ?BlogPost
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id', 'id');
    }

    public function editor(): ?User
    {
        return $this->belongsTo(User::class, 'editado_por', 'id');
    }
}
