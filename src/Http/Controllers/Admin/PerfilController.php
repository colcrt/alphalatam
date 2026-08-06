<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\App;
use App\Core\Auth\Auth;
use App\Core\Auth\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Database\Connection;

class PerfilController
{
    public function show(): void
    {
        $user = Auth::user();

        View::render('admin.perfil', [
            'user' => $user,
        ]);
    }

    public function update(): void
    {
        $request = Request::fromGlobals();
        if (!Csrf::verify($request)) {
            Session::flash('error', 'Token de seguridad inválido. Intente de nuevo.');
            header('Location: ' . url('/admin/mi-perfil'));
            exit;
        }

        $user = Auth::user();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($name)) {
            Session::flash('error', 'El nombre es obligatorio.');
            header('Location: ' . url('/admin/mi-perfil'));
            exit;
        }

        if (empty($email)) {
            Session::flash('error', 'El correo electrónico es obligatorio.');
            header('Location: ' . url('/admin/mi-perfil'));
            exit;
        }

        if ($email !== $user->email) {
            $existing = Connection::table('users')
                ->where('email', $email)
                ->where('id', '!=', $user->id)
                ->first();

            if ($existing) {
                Session::flash('error', 'Este correo electrónico ya está en uso.');
                header('Location: ' . url('/admin/mi-perfil'));
                exit;
            }
        }

        Connection::table('users')
            ->where('id', $user->id)
            ->update([
                'name' => $name,
                'email' => $email,
            ]);

        Session::flash('exito', 'Perfil actualizado correctamente.');
        header('Location: ' . url('/admin/mi-perfil'));
        exit;
    }

    public function updatePassword(): void
    {
        $request = Request::fromGlobals();
        if (!Csrf::verify($request)) {
            Session::flash('error', 'Token de seguridad inválido. Intente de nuevo.');
            header('Location: ' . url('/admin/mi-perfil'));
            exit;
        }

        $user = Auth::user();

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword)) {
            Session::flash('error', 'Debe ingresar su contraseña actual.');
            header('Location: ' . url('/admin/mi-perfil'));
            exit;
        }

        if (!password_verify($currentPassword, $user->password)) {
            Session::flash('error', 'La contraseña actual es incorrecta.');
            header('Location: ' . url('/admin/mi-perfil'));
            exit;
        }

        if (strlen($newPassword) < 8) {
            Session::flash('error', 'La nueva contraseña debe tener al menos 8 caracteres.');
            header('Location: ' . url('/admin/mi-perfil'));
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            Session::flash('error', 'Las contraseñas no coinciden.');
            header('Location: ' . url('/admin/mi-perfil'));
            exit;
        }

        Connection::table('users')
            ->where('id', $user->id)
            ->update([
                'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            ]);

        Session::flash('exito', 'Contraseña actualizada correctamente.');
        header('Location: ' . url('/admin/mi-perfil'));
        exit;
    }

    public function updateAvatar(): void
    {
        $request = Request::fromGlobals();
        if (!Csrf::verify($request)) {
            Session::flash('error', 'Token de seguridad inválido. Intente de nuevo.');
            header('Location: ' . url('/admin/mi-perfil'));
            exit;
        }

        $user = Auth::user();

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Seleccione una imagen válida.');
            header('Location: ' . url('/admin/mi-perfil'));
            exit;
        }

        $archivo = $_FILES['avatar'];

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($archivo['type'], $allowed)) {
            Session::flash('error', 'Tipo de archivo no permitido. Use JPG, PNG, GIF o WebP.');
            header('Location: ' . url('/admin/mi-perfil'));
            exit;
        }

        $maxSize = 2 * 1024 * 1024;
        if ($archivo['size'] > $maxSize) {
            Session::flash('error', 'El archivo excede el tamaño máximo de 2MB.');
            header('Location: ' . url('/admin/mi-perfil'));
            exit;
        }

        if (!extension_loaded('gd') || !(gd_info()['WebP Support'] ?? false)) {
            Session::flash('error', 'El servidor no tiene GD con soporte WebP.');
            header('Location: ' . url('/admin/mi-perfil'));
            exit;
        }

        $dir = dirname(__DIR__, 3) . '/uploads/avatars';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $nombre = 'avatar_' . $user->id . '_' . date('Ymd_His') . '.webp';
        $ruta = $dir . '/' . $nombre;

        if ($archivo['type'] === 'image/webp') {
            if (!move_uploaded_file($archivo['tmp_name'], $ruta)) {
                Session::flash('error', 'Error al guardar la imagen.');
                header('Location: ' . url('/admin/mi-perfil'));
                exit;
            }
        } else {
            $img = match ($archivo['type']) {
                'image/jpeg' => imagecreatefromjpeg($archivo['tmp_name']),
                'image/png'  => imagecreatefrompng($archivo['tmp_name']),
                'image/gif'  => imagecreatefromgif($archivo['tmp_name']),
            };
            if (!$img) {
                Session::flash('error', 'Error al procesar la imagen.');
                header('Location: ' . url('/admin/mi-perfil'));
                exit;
            }

            $size = min(imagesx($img), imagesy($img), 256);
            $tmp = imagecreatetruecolor($size, $size);
            $offsetX = (imagesx($img) - $size) / 2;
            $offsetY = (imagesy($img) - $size) / 2;
            imagecopyresampled($tmp, $img, 0, 0, (int)$offsetX, (int)$offsetY, $size, $size, imagesx($img), imagesy($img));
            imagedestroy($img);

            imagealphablending($tmp, false);
            imagesavealpha($tmp, true);
            $ok = imagewebp($tmp, $ruta, 85);
            imagedestroy($tmp);

            if (!$ok) {
                Session::flash('error', 'Error al convertir la imagen a WebP.');
                header('Location: ' . url('/admin/mi-perfil'));
                exit;
            }
        }

        $avatarPath = 'avatars/' . $nombre;

        if (!empty($user->avatar_path) && file_exists(dirname(__DIR__, 3) . '/uploads/' . $user->avatar_path)) {
            unlink(dirname(__DIR__, 3) . '/uploads/' . $user->avatar_path);
        }

        Connection::table('users')
            ->where('id', $user->id)
            ->update([
                'avatar_path' => $avatarPath,
            ]);

        Session::flash('exito', 'Avatar actualizado correctamente.');
        header('Location: ' . url('/admin/mi-perfil'));
        exit;
    }
}
