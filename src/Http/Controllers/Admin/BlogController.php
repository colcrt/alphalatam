<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth\Auth;
use App\Core\View;

final class BlogController
{
    public function index(): void
    {
        $user = Auth::user();

        View::render('admin.app', [
            'user' => $user,
            'module' => 'blog',
        ]);
    }

    public function create(): void
    {
        $user = Auth::user();

        View::render('admin.blog-form', [
            'user' => $user,
            'postId' => null,
        ]);
    }

    public function edit(int $id): void
    {
        $user = Auth::user();

        View::render('admin.blog-form', [
            'user' => $user,
            'postId' => $id,
        ]);
    }
}
