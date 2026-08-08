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
use App\Services\ConfiguracionService;
use App\Services\EstadisticasService;
use App\Services\BlogService;

class DashboardController
{
    public function index(): void
    {
        $user = Auth::user();
        $estadisticas = App::make(EstadisticasService::class);

        $stats = [];
        $role = $user->role;

        if ($role === 'admin') {
            $stats = $estadisticas->resumen();
            try {
                $stats['solicitudes_borrado'] = App::make(BlogService::class)->contarSolicitudesBorrado();
            } catch (\Throwable $e) {
                $stats['solicitudes_borrado'] = 0;
            }
        } elseif ($role === 'editor') {
            $base = Connection::table('blog_posts')
                ->where('autor_id', $user->id)
                ->whereNull('deleted_at');

            $stats = [
                'mis_posts' => (clone $base)->count(),
                'mis_borradores' => (clone $base)->where('status', 'borrador')->count(),
                'mis_publicados' => (clone $base)->where('status', 'publicado')->count(),
                'mis_noticias' => (clone $base)->where('tipo', 'noticia')->count(),
                'mis_opinion' => (clone $base)->where('tipo', 'opinion')->count(),
                'mis_investigaciones' => (clone $base)->where('tipo', 'investigacion')->count(),
            ];
        } elseif ($role === 'revisor') {
            $stats = [
                'total_publicados' => Connection::table('blog_posts')
                    ->where('status', 'publicado')
                    ->whereNull('deleted_at')
                    ->count(),
                'total_borradores' => Connection::table('blog_posts')
                    ->where('status', 'borrador')
                    ->whereNull('deleted_at')
                    ->count(),
                'comentarios_pendientes' => Connection::table('blog_comentarios')
                    ->where('status', 'pendiente')
                    ->count(),
                'total_categorias' => Connection::table('blog_categorias')
                    ->count(),
            ];
        } else {
            $stats = [
                'total_publicados' => Connection::table('blog_posts')
                    ->where('status', 'publicado')
                    ->whereNull('deleted_at')
                    ->count(),
                'total_usuarios' => Connection::table('users')
                    ->count(),
            ];
        }

        $config = [];
        if ($role === 'admin') {
            $configService = App::make(ConfiguracionService::class);
            $config = [
                'registro_habilitado' => $configService->registroHabilitado(),
                'encuesta' => $configService->obtener('encuesta_activa'),
                'reto' => $configService->obtener('reto_activa'),
            ];
        }

        View::render('admin.dashboard', [
            'user' => $user,
            'stats' => $stats,
            'role' => $role,
            'config' => $config,
        ]);
    }

    public function updateConfig(): void
    {
        $request = Request::fromGlobals();
        if (!Csrf::verify($request)) {
            Session::flash('error', 'Token de seguridad inválido. Intente de nuevo.');
            header('Location: ' . url('/admin/dashboard'));
            exit;
        }

        $user = Auth::user();

        if (!$user->esAdmin()) {
            Session::flash('error', 'No tiene permisos para realizar esta acción.');
            header('Location: ' . url('/admin/dashboard'));
            exit;
        }

        $configService = App::make(ConfiguracionService::class);

        $registroHabilitado = isset($_POST['registro_habilitado']) && $_POST['registro_habilitado'] === '1';
        $configService->guardar('registro_habilitado', $registroHabilitado, 'general');

        $encuestaPregunta = trim((string) ($_POST['encuesta_pregunta'] ?? ''));

        if ($encuestaPregunta === '') {
            $configService->guardar('encuesta_activa', [], 'general');
        } else {
            $opcionesTexto = array_values(array_filter(
                array_map('trim', preg_split('/\r?\n/', (string) ($_POST['encuesta_opciones'] ?? ''))),
                fn ($texto) => $texto !== ''
            ));

            if (count($opcionesTexto) < 2) {
                Session::flash('error', 'La encuesta necesita una pregunta y al menos 2 opciones (una por línea).');
                header('Location: ' . url('/admin/dashboard'));
                exit;
            }

            $encuestaActual = $configService->obtener('encuesta_activa');
            $opcionesPrevias = is_array($encuestaActual) && !empty($encuestaActual['opciones'])
                ? array_values($encuestaActual['opciones'])
                : [];

            $reiniciarVotos = isset($_POST['encuesta_reiniciar']) && $_POST['encuesta_reiniciar'] === '1';

            $opciones = [];
            foreach ($opcionesTexto as $texto) {
                $votos = 0;
                if (!$reiniciarVotos) {
                    foreach ($opcionesPrevias as $opcion) {
                        if (($opcion['texto'] ?? '') === $texto) {
                            $votos = (int) ($opcion['votos'] ?? 0);
                            break;
                        }
                    }
                }
                $opciones[] = ['texto' => $texto, 'votos' => $votos];
            }

            $configService->guardar('encuesta_activa', ['pregunta' => $encuestaPregunta, 'opciones' => $opciones], 'general');
        }

        $retoPregunta = trim((string) ($_POST['reto_pregunta'] ?? ''));

        if ($retoPregunta === '') {
            $configService->guardar('reto_activa', [], 'general');
        } else {
            $opcionesTexto = array_values(array_filter(
                array_map('trim', preg_split('/\r?\n/', (string) ($_POST['reto_opciones'] ?? ''))),
                fn ($texto) => $texto !== ''
            ));

            if (count($opcionesTexto) < 2) {
                Session::flash('error', 'El reto necesita una pregunta y al menos 2 opciones (una por línea).');
                header('Location: ' . url('/admin/dashboard'));
                exit;
            }

            $retoCorrecta = (int) ($_POST['reto_correcta'] ?? 1) - 1;
            if ($retoCorrecta < 0 || $retoCorrecta >= count($opcionesTexto)) {
                Session::flash('error', 'La opción correcta debe estar entre 1 y ' . count($opcionesTexto) . '.');
                header('Location: ' . url('/admin/dashboard'));
                exit;
            }

            $retoActual = $configService->obtener('reto_activa');
            $opcionesPrevias = is_array($retoActual) && !empty($retoActual['opciones'])
                ? array_values($retoActual['opciones'])
                : [];

            $reiniciarVotos = isset($_POST['reto_reiniciar']) && $_POST['reto_reiniciar'] === '1';

            $opciones = [];
            foreach ($opcionesTexto as $texto) {
                $votos = 0;
                if (!$reiniciarVotos) {
                    foreach ($opcionesPrevias as $opcion) {
                        if (($opcion['texto'] ?? '') === $texto) {
                            $votos = (int) ($opcion['votos'] ?? 0);
                            break;
                        }
                    }
                }
                $opciones[] = ['texto' => $texto, 'votos' => $votos];
            }

            $configService->guardar('reto_activa', [
                'pregunta' => $retoPregunta,
                'opciones' => $opciones,
                'respuesta_correcta' => $retoCorrecta,
            ], 'general');
        }

        Session::flash('exito', 'Configuración actualizada correctamente.');
        header('Location: ' . url('/admin/dashboard'));
        exit;
    }
}
