<?php

declare(strict_types=1);

namespace Lynnfly\HyperfConfigCenter;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                Listener\OnPipeMessageListener::class,
                Listener\OnConfigChangedListener::class,
            ],
            'processes' => [
                Process\ConfigFetcherProcess::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config of ',
                    'source' => __DIR__ . '/../publish/captcha.php',
                    'destination' => BASE_PATH . '/config/autoload/config_center.php',
                ],
            ],
        ];
    }
}
