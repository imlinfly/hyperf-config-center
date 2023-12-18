<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/15 15:15:55
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\HyperfConfigCenter\Listener;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnPipeMessage;
use Hyperf\Process\Event\PipeMessage as CustomProcessPipeMessage;
use Lynnfly\HyperfConfigCenter\Contract\PipeMessageInterface;
use Lynnfly\HyperfConfigCenter\Event\ConfigChangedEvent;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class OnPipeMessageListener implements ListenerInterface
{
    protected EventDispatcherInterface $event;

    public function __construct(
        protected StdoutLoggerInterface $logger,
    )
    {
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            OnPipeMessage::class,
            CustomProcessPipeMessage::class,
        ];
    }

    /**
     * process
     * @param object $event
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(object $event): void
    {
        $this->event ??= ApplicationContext::getContainer()->get(EventDispatcherInterface::class);

        if ($event instanceof OnPipeMessage || $event instanceof CustomProcessPipeMessage) {
            if ($event->data instanceof PipeMessageInterface) {
                $this->event->dispatch(new ConfigChangedEvent($event->data->getData()));
            }
        }
    }
}
