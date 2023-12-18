<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/17 17:02:01
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\HyperfConfigCenter\Event;

class ConfigChangedEvent
{
    public function __construct(public array $config)
    {

    }
}
