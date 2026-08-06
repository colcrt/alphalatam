<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Core\Auth\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Database\Connection;

class DenunciaController
{
    public function index(): void
    {
        View::render('public.denuncias.index');
    }

    public function store(): void
    {
        $request = Request::fromGlobals();

        if (!Csrf::verify($request)) {
            Response::withError('/denuncias', 'Token de seguridad inválido. Intente de nuevo.');
            return;
        }

        if (!$this->verifyRecaptcha($request)) {
            $this->guardarOld($request);
            Response::withError('/denuncias', 'Verificación CAPTCHA fallida. Intente de nuevo.');
            return;
        }

        $aceptaTerminos = $request->input('acepta_terminos');

        $validador = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'hechos' => 'required|string|min:50',
            'evidencias' => 'nullable|string|max:4000',
            'email_contacto' => 'nullable|email|max:255',
            'acepta_terminos' => 'required',
        ]);

        if ($validador->fails()) {
            $this->guardarOld($request);
            $mensaje = $aceptaTerminos === null || $aceptaTerminos === ''
                ? 'Debes aceptar el aviso legal y la política de privacidad para enviar tu denuncia.'
                : 'Revisa los datos del formulario: ' . ($validador->firstError() ?? '');
            Response::withError('/denuncias', $mensaje);
            return;
        }

        $titulo = trim((string) $request->input('titulo', ''));
        $hechos = trim((string) $request->input('hechos', ''));
        $evidencias = $this->normalizarEvidencias((string) $request->input('evidencias', ''));
        $email = strtolower(trim((string) $request->input('email_contacto', '')));

        Connection::table('denuncias')->insert([
            'titulo' => $titulo,
            'hechos' => $hechos,
            'evidencias' => $evidencias !== '' ? $evidencias : null,
            'email_contacto' => $email !== '' ? $email : null,
            'acepta_terminos' => 1,
            'status' => 'pendiente',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        foreach (['titulo', 'hechos', 'evidencias', 'email_contacto'] as $campo) {
            Session::forget("_old_{$campo}");
        }

        Response::withSuccess(
            '/denuncias',
            'Tu denuncia fue recibida. Nuestro equipo editorial la revisará y, si procede, la convertirá en una investigación publicable.'
        );
    }

    private function normalizarEvidencias(string $texto): string
    {
        $urls = [];

        foreach (preg_split('/\r?\n/', $texto) as $linea) {
            $linea = trim($linea);
            if ($linea === '') {
                continue;
            }
            $urls[] = $linea;
        }

        return implode("\n", array_unique($urls));
    }

    private function guardarOld(Request $request): void
    {
        foreach (['titulo', 'hechos', 'evidencias', 'email_contacto'] as $campo) {
            Session::set("_old_{$campo}", (string) $request->input($campo, ''));
        }
    }

    private function verifyRecaptcha(Request $request): bool
    {
        $secret = $_ENV['RECAPTCHA_SECRET_KEY'] ?? '';
        if ($secret === '') {
            return true;
        }

        $response = $request->input('g-recaptcha-response', '');
        if ($response === '') {
            return false;
        }

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'secret' => $secret,
                    'response' => $response,
                    'remoteip' => $request->ip(),
                ]),
                'timeout' => 10,
            ],
        ]);

        $verify = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $ctx);
        if ($verify === false) {
            return false;
        }

        $result = json_decode($verify, true);
        return isset($result['success']) && $result['success'] === true;
    }
}
