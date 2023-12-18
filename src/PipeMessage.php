<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/15 15:25:17
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\HyperfConfigCenter;

use Lynnfly\HyperfConfigCenter\Contract\PipeMessageInterface;

class PipeMessage implements PipeMessageInterface
{

    public function __construct(protected array $data)
    {
    }

    /**
     * getData
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
