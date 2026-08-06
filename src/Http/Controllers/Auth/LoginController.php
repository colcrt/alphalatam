<?php
declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Core\View;
use App\Core\Response;
use App\Core\Auth\Auth;
use App\Core\Auth\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Core\Cache;

class LoginController
{
    private const MAX_ATTEMPTS = 10;
    private const RECAPTCHA_THRESHOLD = 5;
    private const LOCKOUT_TTL = 900;

    public function show(): void
    {
        if (Auth::check()) {
            Response::redirect('/admin/dashboard');
            return;
        }

        $ip = $this->getClientIp();
        $attempts = $this->getAttempts($ip);
        $_showRecaptcha = $attempts >= self::RECAPTCHA_THRESHOLD;

        if ($attempts >= self::MAX_ATTEMPTS) {
            Session::flash('error', 'Demasiados intentos fallidos. Espere 15 minutos e intente de nuevo.');
        }

        View::render('auth.login', ['_showRecaptcha' => $_showRecaptcha]);
    }

    public function store(): void
    {
        $request = Request::fromGlobals();

        if (!Csrf::verify($request)) {
            Response::back();
            return;
        }

        $ip = $this->getClientIp();
        $attempts = $this->getAttempts($ip);

        if ($attempts >= self::MAX_ATTEMPTS) {
            Session::flash('error', 'Demasiados intentos fallidos. Espere 15 minutos e intente de nuevo.');
            Response::redirect('/login');
            return;
        }

        if ($attempts >= self::RECAPTCHA_THRESHOLD) {
            if (!$this->verifyRecaptcha($request)) {
                Session::flash('error', 'Verificación CAPTCHA fallida. Intente de nuevo.');
                $this->incrementAttempts($ip);
                Response::redirect('/login');
                return;
            }
        }

        $email = $request->input('email', '');
        $password = $request->input('password', '');

        if (Auth::attemptLogin($email, $password)) {
            $this->clearAttempts($ip);
            Response::redirect('/admin/dashboard');
        } else {
            $this->incrementAttempts($ip);
            Session::flash('error', 'Credenciales incorrectas.');
            Response::redirect('/login');
        }
    }

    private function getClientIp(): string
    {
        $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;
        if ($forwarded) {
            $ips = array_map('trim', explode(',', $forwarded));
            return $ips[0] ?: '0.0.0.0';
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private function getAttempts(string $ip): int
    {
        return (int) Cache::get("login_fails:{$ip}");
    }

    private function incrementAttempts(string $ip): void
    {
        $current = $this->getAttempts($ip);
        Cache::put("login_fails:{$ip}", $current + 1, self::LOCKOUT_TTL);
    }

    private function clearAttempts(string $ip): void
    {
        Cache::forget("login_fails:{$ip}");
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

        $remoteIp = $this->getClientIp();

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'secret' => $secret,
                    'response' => $response,
                    'remoteip' => $remoteIp,
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
