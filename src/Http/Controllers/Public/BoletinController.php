<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Core\Auth\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Database\Connection;

class BoletinController
{
    public function suscribir(): void
    {
        $request = Request::fromGlobals();

        if (!Csrf::verify($request)) {
            Response::withError('/', 'Token de seguridad inválido. Intente de nuevo.');
            return;
        }

        $validador = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validador->fails()) {
            Response::withError('/', 'Ingresa un correo electrónico válido.');
            return;
        }

        $email = strtolower(trim((string) $request->input('email', '')));

        $existe = Connection::table('suscriptores')
            ->where('email', $email)
            ->first();

        if ($existe) {
            Response::withError('/', 'Este correo ya está suscrito al boletín.');
            return;
        }

        Connection::table('suscriptores')->insert([
            'email' => $email,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        Response::withSuccess('/', '¡Gracias por suscribirte! Recibirás el boletín diario.');
    }
}
