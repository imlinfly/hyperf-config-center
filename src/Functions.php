<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/17 17:15:05
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

use Hyperf\Context\ApplicationContext;
use Lynnfly\HyperfConfigCenter\ConfigService;

/**
 * 获取配置
 * @param string|null $key
 * @param mixed|null $default
 * @return mixed
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */
function setting(string $key = null, mixed $default = null): mixed
{
    $name = ApplicationContext::getContainer()->get(ConfigService::class)->getConfigFileName();

    if ($key === null) {
        return Hyperf\Config\config($name);
    }

    return Hyperf\Config\config($name . '.' . $key, $default);
}
