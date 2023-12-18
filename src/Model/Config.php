<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/15 15:33:26
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\HyperfConfigCenter\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property string $id 配置key
 * @property array $value 配置值
 */
class Config extends Model
{
    protected ?string $table = 'config';

    protected array $fillable = ['name', 'value'];

    protected array $casts = ['name' => 'string', 'value' => 'array'];

    protected string $primaryKey = 'name';

    public bool $timestamps = false;
}
