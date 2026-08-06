<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Request;
use App\Core\Response;

class RoleMiddleware
{
    public function handle(Request $request, string ...$roles): bool
    {
        if (!Auth::check()) {
            Response::redirect('/login');
            return false;
        }

        if (!empty($roles) && !Auth::hasRole(...$roles)) {
            Response::forbidden();
            return false;
        }

        return true;
    }
}
