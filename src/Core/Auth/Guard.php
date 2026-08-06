<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class Guard
{
    public function handle(Request $request): bool
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                Response::json(['message' => 'No autenticado.'], 401);
                return false;
            }
            Session::flash('error', 'Debe iniciar sesión para continuar.');
            Response::redirect('/login');
            return false;
        }
        return true;
    }
}

class RoleGuard
{
    public function handle(Request $request, string ...$roles): bool
    {
        if (!Auth::check()) {
            Response::redirect('/login');
            return false;
        }

        if (!Auth::hasRole(...$roles)) {
            Response::forbidden();
            return false;
        }

        return true;
    }
}
