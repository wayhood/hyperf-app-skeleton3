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

use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;

abstract class AbstractService
{
    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(LoggerFactory $loggerFactory, protected ContainerInterface $container)
    {
        $this->container = $container;
        // 第一个参数对应日志的 name, 第二个参数对应 config/autoload/logger.php 内的 key
        $this->logger = $loggerFactory->get('log', 'default');
    }

    public function getParamsMd5(array $params)
    {
        return md5(serialize($params));
    }

    public function getStringMd5($param)
    {
        return md5($param);
    }

    public function getArrayShift($array)
    {
        if (is_array($array)) {
            $ret = array_shift($array);
        } else {
            $ret = $array;
        }
        return $ret;
    }

    public function getWhereInString($array)
    {
        $new = [];
        foreach ($array as $a) {
            $new[] = "'" . $a . "'";
        }
        return join(',', $new);
    }

    // 二维数组去掉重复值
    public function a_array_unique($array)
    {
        $out = [];

        foreach ($array as $key => $value) {
            if (! in_array($value, $out)) {
                $out[$key] = $value;
            }
        }

        return array_values($out);
    }
}
