<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Session;
use App\Models\User;
use App\Database\Connection;

class Auth
{
    private static ?User $user = null;

    public static function attempt(string $email, string $password, bool $remember = false): bool
    {
        $user = static::findByEmail($email);
        if (!$user || !password_verify($password, $user->password)) {
            return false;
        }

        Session::regenerate();
        Session::set('user_id', $user->id);

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            Session::set('remember_token', $token);
            Connection::table('users')
                ->where('id', $user->id)
                ->update(['remember_token' => $token]);
        }

        static::$user = $user;
        return true;
    }

    public static function register(array $data): User
    {
        $datos = [
            'name' => (string) ($data['name'] ?? ''),
            'email' => (string) ($data['email'] ?? ''),
            'password' => password_hash((string) ($data['password'] ?? ''), PASSWORD_BCRYPT, ['cost' => 12]),
            'role' => 'editor',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $id = Connection::table('users')->insert($datos);
        $datos['id'] = $id;

        return User::fromRow((object) $datos);
    }

    public static function logout(): void
    {
        Session::forget('user_id');
        Session::forget('remember_token');
        Session::regenerate();
        static::$user = null;
    }

    public static function check(): bool
    {
        return static::user() !== null;
    }

    public static function guest(): bool
    {
        return !static::check();
    }

    public static function user(): ?User
    {
        if (static::$user !== null) {
            return static::$user;
        }

        $userId = Session::get('user_id');
        if (!$userId) {
            return null;
        }

        $row = Connection::table('users')->where('id', $userId)->first();
        if (!$row) {
            Session::forget('user_id');
            return null;
        }

        static::$user = User::fromRow($row);

        return static::$user;
    }

    public static function id(): ?int
    {
        $user = static::user();
        return $user ? (int) $user->id : null;
    }

    public static function hasRole(string ...$roles): bool
    {
        $user = static::user();
        if (!$user) return false;
        return in_array($user->role, $roles);
    }

    public static function isAdmin(): bool
    {
        return static::hasRole('admin');
    }

    public static function canPublish(): bool
    {
        return static::hasRole('admin', 'editor');
    }

    public static function findByEmail(string $email): ?User
    {
        $row = Connection::table('users')->where('email', $email)->first();
        if (!$row) return null;
        return User::fromRow($row);
    }

    public static function attemptLogin(string $email, string $password): bool
    {
        $user = static::findByEmail($email);
        if (!$user || !password_verify($password, $user->password)) {
            return false;
        }
        Session::regenerate();
        Session::set('user_id', $user->id);
        static::$user = $user;
        return true;
    }
}
