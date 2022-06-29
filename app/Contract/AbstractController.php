<?php

declare(strict_types=1);
/**
 * .__
 * |__|  ____    ______
 * |  |_/ __ \  /  ___/
 * |  |\  ___/  \___ \
 * |__| \___  >/____  >
 *          \/      \/
 */
namespace App\Contract;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{
    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;
}
