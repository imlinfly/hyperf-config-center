<?php
return [
    // 存放配置的模型
    'model' => Lynnfly\HyperfConfigCenter\Model\Config::class,
    // 存放配置的文件名 BASE_PATH . '/config/autoload/xxx.php'
    'config_file' => 'setting',
    // 检查配置变化的间隔时间
    'interval' => 5,
];
