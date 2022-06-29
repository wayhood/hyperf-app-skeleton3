<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST'], "/",
    'Wayhood\HyperfAction\Controller\MainController@index',
    ['middleware' => [\Wayhood\HyperfAction\Middleware\ActionMiddleware::class]]);

Router::get('/doc',
    'Wayhood\HyperfAction\Controller\MainController@doc');

Router::get('/favicon.ico', function () {
    return '';
});
