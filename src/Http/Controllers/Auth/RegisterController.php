<?php
declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Core\App;
use App\Core\View;
use App\Core\Response;
use App\Core\Auth\Auth;
use App\Core\Auth\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validator;
use App\Services\ConfiguracionService;

class RegisterController
{
    public function show(): void
    {
        if (Auth::check()) {
            Response::redirect('/admin/dashboard');
            return;
        }

        $config = App::make(ConfiguracionService::class);
        if (!$config->registroHabilitado()) {
            Session::flash('error', 'El registro de nuevos usuarios está deshabilitado.');
            Response::redirect('/login');
            return;
        }

        View::render('auth.register');
    }

    public function store(): void
    {
        $request = Request::fromGlobals();

        if (!Csrf::verify($request)) {
            Response::back();
            return;
        }

        $config = App::make(ConfiguracionService::class);
        if (!$config->registroHabilitado()) {
            Session::flash('error', 'El registro de nuevos usuarios está deshabilitado.');
            Response::redirect('/login');
            return;
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $request->only(['name', 'email']));
            Response::redirect('/register');
            return;
        }

        $user = Auth::register([
            'name' => $request->input('name', ''),
            'email' => $request->input('email', ''),
            'password' => $request->input('password', ''),
            'role' => 'editor',
        ]);

        Session::set('user_id', $user->id);
        Session::regenerate();
        Session::flash('success', 'Cuenta creada exitosamente.');
        Response::redirect('/admin/dashboard');
    }
}
