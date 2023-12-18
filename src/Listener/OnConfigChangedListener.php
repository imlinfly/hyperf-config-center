<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/15 15:15:55
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\HyperfConfigCenter\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Lynnfly\HyperfConfigCenter\ConfigService;
use Lynnfly\HyperfConfigCenter\Event\ConfigChangedEvent;

class OnConfigChangedListener implements ListenerInterface
{
    public function __construct(
        protected ConfigInterface $config,
        protected ConfigService   $configService,
    )
    {
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            ConfigChangedEvent::class,
        ];
    }

    /**
     * process
     * @param object $event
     * @return void
     */
    public function process(object $event): void
    {
        /** @var ConfigChangedEvent $event */
        $config = $event->config;

        $name = $this->configService->getConfigFileName();

        $this->config->set($name, $config);
    }
}
