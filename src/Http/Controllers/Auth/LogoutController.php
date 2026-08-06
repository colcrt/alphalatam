<?php
declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Core\Response;
use App\Core\Auth\Auth;
use App\Core\Session;

class LogoutController
{
    public function store(): void
    {
        Auth::logout();
        Session::flush();
        Session::regenerate();
        Response::redirect('/');
    }
}
