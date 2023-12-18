<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/15 15:23:16
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\HyperfConfigCenter\Contract;

interface PipeMessageInterface
{
    public function getData(): array;
}
