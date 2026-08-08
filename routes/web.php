<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Router;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\BuscadorController;
use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\EncuestaController;
use App\Http\Controllers\Public\RetoController;
use App\Http\Controllers\Public\BoletinController;
use App\Http\Controllers\Public\DenunciaController;
use App\Http\Controllers\Admin\DenunciaController as AdminDenunciaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\ComentarioController;
use App\Http\Controllers\Admin\PerfilController;
use App\Http\Controllers\Public\SitemapController;

$router = App::make(Router::class);

// --- SEO routes ---
$router->get('/sitemap', [new SitemapController(), 'index'])->name('sitemap');

// --- Public routes ---
$router->get('/', [new HomeController(), 'index'])->name('home');
$router->get('/buscar', [new BuscadorController(), 'index'])->name('buscador.index');

$router->get('/blog', [new BlogController(), 'index'])->name('blog.index');
$router->get('/blog/{tipo}', [new BlogController(), 'index'])->name('blog.tipo');
$router->get('/blog/post/{slug}', [new BlogController(), 'show'])->name('blog.show');

$router->post('/encuesta/votar', [new EncuestaController(), 'votar'])->name('encuesta.votar');

$router->post('/reto/votar', [new RetoController(), 'votar'])->name('reto.votar');

$router->post('/boletin/suscribir', [new BoletinController(), 'suscribir'])->name('boletin.suscribir');

// --- Denuncias ciudadanas ---
$router->get('/denuncias', [new DenunciaController(), 'index'])->name('denuncias.index');
$router->post('/denuncias', [new DenunciaController(), 'store'])->name('denuncias.store');

// --- Legal routes ---
$router->get('/legal/quienes-somos', function () {
    \App\Core\View::render('public.paginas.quienes-somos');
})->name('legal.quienes-somos');
$router->get('/legal/politicas-legales', function () {
    \App\Core\View::render('public.paginas.politicas-legales');
})->name('legal.politicas-legales');
$router->get('/legal/politica-editorial', function () {
    \App\Core\View::render('public.paginas.politica-editorial');
})->name('legal.politica-editorial');
$router->get('/legal/transparencia', function () {
    \App\Core\View::render('public.paginas.transparencia');
})->name('legal.transparencia');
$router->get('/legal/politica-privacidad', function () {
    \App\Core\View::render('public.paginas.politica-privacidad');
})->name('legal.politica-privacidad');

// --- Auth routes ---
$router->get('/login', [new LoginController(), 'show'])->name('login');
$router->post('/login', [new LoginController(), 'store']);
$router->get('/register', [new RegisterController(), 'show'])->name('register');
$router->post('/register', [new RegisterController(), 'store']);
$router->post('/logout', [new LogoutController(), 'store'])->name('logout');
$router->get('/forgot-password', [new ForgotPasswordController(), 'show'])->name('password.request');
$router->post('/forgot-password', [new ForgotPasswordController(), 'store']);

// --- Admin routes ---
$router->group(['prefix' => 'admin', 'middleware' => [\App\Core\Auth\AuthMiddleware::class]], function (Router $router) {
    $router->get('/dashboard', [new DashboardController(), 'index'])->name('admin.dashboard');

    // Blog CRUD
    $router->get('/blog', [new AdminBlogController(), 'index'])->name('admin.blog.index');
    $router->get('/blog/crear', [new AdminBlogController(), 'create'])->name('admin.blog.create');
    $router->get('/blog/borrados', [new AdminBlogController(), 'borrados'])->name('admin.blog.borrados');
    $router->get('/blog/{id}/editar', [new AdminBlogController(), 'edit'])->name('admin.blog.edit');
    $router->post('/blog/{id}/aprobar-borrado', [new AdminBlogController(), 'aprobarBorrado'])->name('admin.blog.borrados.aprobar');
    $router->post('/blog/{id}/rechazar-borrado', [new AdminBlogController(), 'rechazarBorrado'])->name('admin.blog.borrados.rechazar');

    // Categorías
    $router->get('/categorias', function () {
        return App::make(CategoriaController::class)->index();
    })->name('admin.categorias.index');
    $router->get('/categorias/crear', function () {
        return App::make(CategoriaController::class)->create();
    })->name('admin.categorias.create');
    $router->post('/categorias', function () {
        return App::make(CategoriaController::class)->store();
    })->name('admin.categorias.store');
    $router->get('/categorias/{id}/editar', function (string $id) {
        return App::make(CategoriaController::class)->edit((int) $id);
    })->name('admin.categorias.edit');
    $router->put('/categorias/{id}', function (string $id) {
        return App::make(CategoriaController::class)->update((int) $id);
    })->name('admin.categorias.update');
    $router->delete('/categorias/{id}', function (string $id) {
        return App::make(CategoriaController::class)->destroy((int) $id);
    })->name('admin.categorias.destroy');

    // Comentarios
    $router->get('/comentarios', function () {
        return App::make(ComentarioController::class)->index();
    })->name('admin.comentarios.index');
    $router->post('/comentarios/{id}/aprobar', function (string $id) {
        return App::make(ComentarioController::class)->approve((int) $id);
    })->name('admin.comentarios.approve');
    $router->post('/comentarios/{id}/rechazar', function (string $id) {
        return App::make(ComentarioController::class)->reject((int) $id);
    })->name('admin.comentarios.reject');
    $router->delete('/comentarios/{id}', function (string $id) {
        return App::make(ComentarioController::class)->destroy((int) $id);
    })->name('admin.comentarios.destroy');

    // Denuncias ciudadanas
    $router->get('/denuncias', [new AdminDenunciaController(), 'index'])->name('admin.denuncias.index');
    $router->get('/denuncias/{id}', [new AdminDenunciaController(), 'show'])->name('admin.denuncias.show');
    $router->post('/denuncias/{id}/estado', [new AdminDenunciaController(), 'cambiarEstado'])->name('admin.denuncias.estado');
    $router->post('/denuncias/{id}/convertir', [new AdminDenunciaController(), 'convertir'])->name('admin.denuncias.convertir');
    $router->delete('/denuncias/{id}', [new AdminDenunciaController(), 'destroy'])->name('admin.denuncias.destroy');

    // 2FA
    $router->get('/two-factor', [new TwoFactorController(), 'show'])->name('admin.two-factor.show');
    $router->post('/two-factor', [new TwoFactorController(), 'enable'])->name('admin.two-factor.enable');
    $router->delete('/two-factor', [new TwoFactorController(), 'disable'])->name('admin.two-factor.disable');

    // Mi Perfil
    $router->get('/mi-perfil', [new PerfilController(), 'show'])->name('admin.perfil.show');
    $router->post('/mi-perfil', [new PerfilController(), 'update'])->name('admin.perfil.update');
    $router->post('/mi-perfil/password', [new PerfilController(), 'updatePassword'])->name('admin.perfil.password');
    $router->post('/mi-perfil/avatar', [new PerfilController(), 'updateAvatar'])->name('admin.perfil.avatar');

    // Configuración
    $router->post('/configuracion', function () {
        return App::make(DashboardController::class)->updateConfig();
    })->name('admin.configuracion.update');

    // Admin API for Vue.js
    $router->group(['prefix' => 'api'], function (Router $router) {
        $router->get('/blog', function () {
            return App::make(\App\Http\Api\Controllers\BlogApiController::class)->index();
        });
        $router->post('/blog', function () {
            return App::make(\App\Http\Api\Controllers\BlogApiController::class)->store();
        });
        $router->put('/blog/{id}', function (string $id) {
            return App::make(\App\Http\Api\Controllers\BlogApiController::class)->update($id);
        });
        $router->delete('/blog/{id}', function (string $id) {
            return App::make(\App\Http\Api\Controllers\BlogApiController::class)->destroy($id);
        });
        $router->post('/blog/{id}/publicar', function (string $id) {
            return App::make(\App\Http\Api\Controllers\BlogApiController::class)->publish($id);
        });

        $router->get('/categorias', function () {
            return App::make(\App\Http\Api\Controllers\CategoriaApiController::class)->index();
        });
        $router->post('/categorias', function () {
            return App::make(\App\Http\Api\Controllers\CategoriaApiController::class)->store();
        });
        $router->put('/categorias/{id}', function (string $id) {
            return App::make(\App\Http\Api\Controllers\CategoriaApiController::class)->update($id);
        });
        $router->delete('/categorias/{id}', function (string $id) {
            return App::make(\App\Http\Api\Controllers\CategoriaApiController::class)->destroy($id);
        });

        $router->get('/comentarios', function () {
            return App::make(\App\Http\Api\Controllers\ComentarioApiController::class)->index();
        });
        $router->post('/comentarios/{id}/aprobar', function (string $id) {
            return App::make(\App\Http\Api\Controllers\ComentarioApiController::class)->approve($id);
        });
        $router->post('/comentarios/{id}/rechazar', function (string $id) {
            return App::make(\App\Http\Api\Controllers\ComentarioApiController::class)->reject($id);
        });
        $router->delete('/comentarios/{id}', function (string $id) {
            return App::make(\App\Http\Api\Controllers\ComentarioApiController::class)->destroy($id);
        });

        // Global search
        $router->get('/search', function () {
            return App::make(\App\Http\Api\Controllers\SearchApiController::class)->buscar();
        });

        // oEmbed resolver
        $router->post('/oembed/resolve', function () {
            return App::make(\App\Http\Api\Controllers\OEmbedController::class)->resolve();
        });
    });
});
