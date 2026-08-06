<?php

declare(strict_types=1);

use App\Core\App;

App::bind('App\Repositories\Contracts\BlogRepositoryInterface', 'App\Repositories\Eloquent\EloquentBlogRepository');
