<?php
declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Core\View;
use App\Core\Response;
use App\Core\Auth\Csrf;
use App\Core\Request;
use App\Core\Session;

class ForgotPasswordController
{
    public function show(): void
    {
        View::render('auth.forgot-password');
    }

    public function store(): void
    {
        $request = Request::fromGlobals();

        if (!Csrf::verify($request)) {
            Response::back();
            return;
        }

        $email = $request->input('email', '');

        if (empty($email)) {
            Session::flash('error', 'El correo electrónico es obligatorio.');
            Response::redirect('/forgot-password');
            return;
        }

        Session::flash('success', 'Si el correo está registrado, recibirá un enlace de recuperación.');
        Response::redirect('/forgot-password');
    }
}
