<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\App;
use App\Core\Auth\Auth;
use App\Core\Auth\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Database\Connection;
use App\Services\TwoFactorService;

class TwoFactorController
{
    public function show(): void
    {
        $user = Auth::user();
        $twoFactor = App::make(TwoFactorService::class);

        $secret = $twoFactor->generarSecret();
        $qrCodeUrl = $twoFactor->obtenerQrCodeUrl($user->email, $secret);
        $recoveryCodes = $twoFactor->generarRecoveryCodes();

        View::render('admin.two-factor', [
            'user' => $user,
            'secret' => $secret,
            'qrCodeUrl' => $qrCodeUrl,
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    public function enable(): void
    {
        $request = Request::fromGlobals();

        if (!Csrf::verify($request)) {
            Response::redirect('/admin/two-factor');
            return;
        }

        $user = Auth::user();
        $twoFactor = App::make(TwoFactorService::class);

        $code = $request->input('code', '');
        $secret = $request->input('secret', '');

        if (empty($code) || empty($secret)) {
            $qrCodeUrl = $twoFactor->obtenerQrCodeUrl($user->email, $secret);
            View::render('admin.two-factor', [
                'user' => $user,
                'secret' => $secret,
                'qrCodeUrl' => $qrCodeUrl,
                'recoveryCodes' => [],
                'error' => 'Código de verificación requerido.',
            ]);
            return;
        }

        if (!$twoFactor->verificarCodigo($secret, $code)) {
            $qrCodeUrl = $twoFactor->obtenerQrCodeUrl($user->email, $secret);
            View::render('admin.two-factor', [
                'user' => $user,
                'secret' => $secret,
                'qrCodeUrl' => $qrCodeUrl,
                'recoveryCodes' => [],
                'error' => 'Código de verificación inválido.',
            ]);
            return;
        }

        $encryptedSecret = $twoFactor->encriptarSecret($secret);
        $recoveryCodes = $twoFactor->generarRecoveryCodes();
        $encryptedRecovery = $twoFactor->encriptarRecoveryCodes($recoveryCodes);

            Connection::table('users')
            ->where('id', $user->id)
            ->update([
                'two_factor_secret' => $encryptedSecret,
                'two_factor_recovery_codes' => $encryptedRecovery,
            ]);

        Session::flash('exito', 'Autenticación de dos factores activada correctamente.');
        Response::redirect('/admin/two-factor');
    }

    public function disable(): void
    {
        $request = Request::fromGlobals();

        if (!Csrf::verify($request)) {
            Response::redirect('/admin/two-factor');
            return;
        }

        $user = Auth::user();

            Connection::table('users')
            ->where('id', $user->id)
            ->update([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
            ]);

        Session::flash('exito', 'Autenticación de dos factores desactivada.');
        Response::redirect('/admin/two-factor');
    }
}
