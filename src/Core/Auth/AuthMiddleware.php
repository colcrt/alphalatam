<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware
{
    public function handle(Request $request): bool
    {
        if (!Auth::check()) {
            Session::flash('error', 'Debe iniciar sesión para acceder al panel.');
            Response::redirect('/login');
            return false;
        }

        return true;
    }
}
