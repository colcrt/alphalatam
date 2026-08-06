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
use App\Services\BlogService;

class DenunciaController
{
    private const ESTADOS_GESTION = ['pendiente', 'en_revision', 'rechazada'];
    private const ESTADOS_VALIDOS = ['pendiente', 'en_revision', 'convertida', 'rechazada'];

    public function index(): void
    {
        $user = Auth::user();
        $this->autorizarGestion($user);

        $status = (string) ($_GET['status'] ?? '');

        $query = Connection::table('denuncias');
        if ($status !== '' && in_array($status, self::ESTADOS_VALIDOS, true)) {
            $query = $query->where('status', $status);
        }

        $paginacion = $query
            ->orderByDesc('created_at')
            ->paginate(20, (int) ($_GET['page'] ?? 1));

        View::render('admin.denuncias-index', [
            'user' => $user,
            'denuncias' => $paginacion['data'],
            'paginacion' => $paginacion,
            'statusFiltro' => $status,
        ]);
    }

    public function show(string $id): void
    {
        $user = Auth::user();
        $this->autorizarGestion($user);

        $denuncia = Connection::table('denuncias')
            ->where('id', (int) $id)
            ->first();

        if (!$denuncia) {
            Session::flash('error', 'Denuncia no encontrada.');
            header('Location: ' . url('/admin/denuncias'));
            exit;
        }

        $post = null;
        if ($denuncia->blog_post_id) {
            $post = Connection::table('blog_posts')
                ->where('id', (int) $denuncia->blog_post_id)
                ->first();
        }

        View::render('admin.denuncias-show', [
            'user' => $user,
            'denuncia' => $denuncia,
            'post' => $post,
        ]);
    }

    public function cambiarEstado(string $id): void
    {
        $request = Request::fromGlobals();
        if (!Csrf::verify($request)) {
            Session::flash('error', 'Token de seguridad inválido. Intente de nuevo.');
            header('Location: ' . url('/admin/denuncias'));
            exit;
        }

        $user = Auth::user();
        $this->autorizarGestion($user);

        $estado = (string) $request->input('estado', '');
        if (!in_array($estado, self::ESTADOS_GESTION, true)) {
            Session::flash('error', 'Estado inválido.');
            header('Location: ' . url('/admin/denuncias'));
            exit;
        }

        $denuncia = Connection::table('denuncias')
            ->where('id', (int) $id)
            ->first();

        if (!$denuncia) {
            Session::flash('error', 'Denuncia no encontrada.');
            header('Location: ' . url('/admin/denuncias'));
            exit;
        }

        Connection::table('denuncias')
            ->where('id', (int) $id)
            ->update([
                'status' => $estado,
                'revisado_por' => Auth::id(),
                'revisado_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        Session::flash('exito', 'Estado actualizado a ' . $this->etiquetaEstado($estado) . '.');
        header('Location: ' . url('/admin/denuncias'));
        exit;
    }

    public function convertir(string $id): void
    {
        $request = Request::fromGlobals();
        if (!Csrf::verify($request)) {
            Session::flash('error', 'Token de seguridad inválido. Intente de nuevo.');
            header('Location: ' . url('/admin/denuncias'));
            exit;
        }

        $user = Auth::user();
        if (!$user->puedePublicar()) {
            Session::flash('error', 'No tienes permisos para convertir denuncias en artículos.');
            header('Location: ' . url('/admin/denuncias'));
            exit;
        }

        $denuncia = Connection::table('denuncias')
            ->where('id', (int) $id)
            ->first();

        if (!$denuncia) {
            Session::flash('error', 'Denuncia no encontrada.');
            header('Location: ' . url('/admin/denuncias'));
            exit;
        }

        if (($denuncia->status ?? '') === 'convertida' && $denuncia->blog_post_id) {
            Session::flash('error', 'Esta denuncia ya fue convertida en artículo.');
            header('Location: ' . url('/admin/denuncias'));
            exit;
        }

        try {
            $blogService = App::make(BlogService::class);
            $post = $blogService->crear([
                'titulo' => trim((string) $denuncia->titulo),
                'extracto' => str_limit(trim((string) $denuncia->hechos), 150),
                'contenido' => $this->armarContenido($denuncia),
                'tipo' => 'investigacion',
                'status' => 'borrador',
            ]);

            Connection::table('denuncias')
                ->where('id', (int) $id)
                ->update([
                    'status' => 'convertida',
                    'blog_post_id' => (int) $post->id,
                    'revisado_por' => Auth::id(),
                    'revisado_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            Session::flash('exito', 'Denuncia convertida en borrador de investigación. Edítala antes de publicar.');
            header('Location: ' . url('/admin/blog/' . $post->id . '/editar'));
            exit;
        } catch (\Exception $e) {
            Session::flash('error', 'Error al convertir: ' . $e->getMessage());
            header('Location: ' . url('/admin/denuncias'));
            exit;
        }
    }

    public function destroy(string $id): void
    {
        $request = Request::fromGlobals();
        if (!Csrf::verify($request)) {
            Session::flash('error', 'Token de seguridad inválido. Intente de nuevo.');
            header('Location: ' . url('/admin/denuncias'));
            exit;
        }

        $user = Auth::user();
        if (!$user->esAdmin()) {
            Session::flash('error', 'No tienes permisos para eliminar denuncias.');
            header('Location: ' . url('/admin/denuncias'));
            exit;
        }

        $denuncia = Connection::table('denuncias')
            ->where('id', (int) $id)
            ->first();

        if (!$denuncia) {
            Session::flash('error', 'Denuncia no encontrada.');
            header('Location: ' . url('/admin/denuncias'));
            exit;
        }

        Connection::table('denuncias')
            ->where('id', (int) $id)
            ->delete();

        Session::flash('exito', 'Denuncia eliminada.');
        header('Location: ' . url('/admin/denuncias'));
        exit;
    }

    private function armarContenido(object $denuncia): string
    {
        $html = '';

        foreach (preg_split('/\r?\n/', (string) $denuncia->hechos) as $parrafo) {
            $parrafo = trim($parrafo);
            if ($parrafo === '') {
                continue;
            }
            $html .= '<p>' . esc($parrafo) . '</p>';
        }

        $evidencias = trim((string) $denuncia->evidencias);
        if ($evidencias !== '') {
            $html .= '<h3>Evidencias</h3><ul>';
            foreach (preg_split('/\r?\n/', $evidencias) as $url) {
                $url = trim($url);
                if ($url === '') {
                    continue;
                }
                $html .= '<li><a href="' . esc($url) . '" target="_blank" rel="noopener nofollow">' . esc($url) . '</a></li>';
            }
            $html .= '</ul>';
        }

        return $html;
    }

    private function autorizarGestion(object $user): void
    {
        if (!$user->esAdmin() && ($user->role ?? '') !== 'revisor') {
            Session::flash('error', 'No tienes permisos para gestionar denuncias.');
            header('Location: ' . url('/admin/dashboard'));
            exit;
        }
    }

    private function etiquetaEstado(string $estado): string
    {
        return match ($estado) {
            'pendiente' => 'pendiente',
            'en_revision' => 'en revisión',
            'convertida' => 'convertida',
            'rechazada' => 'rechazada',
            default => $estado,
        };
    }
}
