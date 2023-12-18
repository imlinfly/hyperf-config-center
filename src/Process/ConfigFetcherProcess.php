<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/15 15:14:34
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\HyperfConfigCenter\Process;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessCollector;
use Hyperf\Process\ProcessManager;
use Lynnfly\HyperfConfigCenter\ConfigService;
use Lynnfly\HyperfConfigCenter\Contract\PipeMessageInterface;
use Lynnfly\HyperfConfigCenter\PipeMessage;
use Psr\Container\ContainerInterface;
use Swoole\Process;
use Swoole\Server;
use Throwable;

class ConfigFetcherProcess extends AbstractProcess
{
    public string $name = 'config-center-fetcher';

    /** @var Server */
    public mixed $server;

    public function __construct(
        ContainerInterface              $container,
        protected ConfigService         $service,
        protected StdoutLoggerInterface $logger,
    )
    {
        parent::__construct($container);
    }

    public function bind($server): void
    {
        $this->server = $server;
        parent::bind($server);
    }

    /**
     * 进程启动时回调
     * @return void
     */
    public function handle(): void
    {
        $this->listener();

        while (ProcessManager::isRunning()) {
            Coroutine::sleep(1);
        }
    }

    /**
     * 监听配置变化
     * @return void
     */
    public function listener(): void
    {
        Coroutine::create(function () {
            $interval = $this->service->getInterval();
            $prevVersion = '';
            while (true) {
                try {
                    $coordinator = CoordinatorManager::until(Constants::WORKER_EXIT);
                    $workerExited = $coordinator->yield($interval);
                    if ($workerExited) {
                        break;
                    }
                    $version = $this->service->getVersion();
                    if ($version !== $prevVersion) {
                        $this->syncConfig();
                    }
                    $prevVersion = $version;
                } catch (Throwable $exception) {
                    $this->logger->error((string)$exception);
                    throw $exception;
                }
            }
        });
    }

    /**
     * 同步配置
     * @return void
     */
    protected function syncConfig(): void
    {
        $config = $this->service->syncConfigToFile();

        $message = new PipeMessage($config);
        $this->shareMessageToWorkers($message);
        $this->shareMessageToCustomProcesses($message);
    }

    /**
     * 向所有worker进程发送消息
     * @param PipeMessageInterface $message
     * @return void
     */
    protected function shareMessageToWorkers(PipeMessageInterface $message): void
    {
        if ($this->server instanceof Server) {
            $workerCount = $this->server->setting['worker_num'] + ($this->server->setting['task_worker_num'] ?? 0) - 1;
            for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
                $this->server->sendMessage($message, $workerId);
            }
        }
    }

    /**
     * 向所有自定义进程发送消息
     * @param PipeMessageInterface $message
     * @return void
     */
    protected function shareMessageToCustomProcesses(PipeMessageInterface $message): void
    {
        $processes = ProcessCollector::all();
        if ($processes) {
            $string = serialize($message);
            /** @var Process $process */
            foreach ($processes as $process) {
                $result = $process->exportSocket()->send($string, 10);
                if ($result === false) {
                    $this->logger->error('Configuration synchronization failed. Please restart the server.');
                }
            }
        }
    }
}
