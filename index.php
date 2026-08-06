<?php

declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
ob_start();

require __DIR__ . '/bootstrap/env.php';

require BASE_PATH . '/src/Helpers/helpers.php';

use App\Core\App;
use App\Core\Router;
use App\Core\Request;
use App\Core\View;
use App\Core\Cache;

$sessionLifetime = (int) ($_ENV['SESSION_LIFETIME'] ?? 120);
session_set_cookie_params([
    'lifetime' => $sessionLifetime * 60,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

App::singleton('config', require BASE_PATH . '/config/app.php');
App::singleton('db_config', require BASE_PATH . '/config/database.php');

Cache::init(BASE_PATH . '/storage/cache');

View::init(BASE_PATH . '/templates');

require BASE_PATH . '/bootstrap/repositories.php';

require BASE_PATH . '/routes/web.php';

$router = App::make(Router::class);
$request = Request::fromGlobals();

$router->dispatch($request);
