<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth\Auth;
use App\Core\View;

final class InstitucionController
{
    public function index(): void
    {
        $user = Auth::user();

        View::render('admin.app', [
            'user' => $user,
            'module' => 'institucion',
        ]);
    }
}
