<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Session;
use App\Core\Request;

class Csrf
{
    public static function token(): string
    {
        if (!Session::has('_csrf_token')) {
            Session::set('_csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('_csrf_token');
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . esc(self::token()) . '">';
    }

    public static function verify(Request $request): bool
    {
        $token = $request->input('_token')
            ?? $request->header('X-CSRF-TOKEN')
            ?? '';
        return hash_equals(self::token(), $token);
    }
}
