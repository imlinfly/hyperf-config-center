<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2023/12/15 15:41:10
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace Lynnfly\HyperfConfigCenter;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Model\Model;
use InvalidArgumentException;
use Lynnfly\HyperfConfigCenter\Model\Config;
use RuntimeException;
use Throwable;

class ConfigService
{
    protected array $config;
    protected string $versionKey = '__setting_version__';

    public function __construct(
        ConfigInterface                 $config,
        protected StdoutLoggerInterface $logger,
    )
    {
        $this->config = $config->get('config_center', [
            // 存放配置的模型
            'model' => \Lynnfly\HyperfConfigCenter\Model\Config::class,
            // 存放配置的文件名 BASE_PATH . '/config/autoload/xxx.php'
            'config_file' => 'setting',
            // 检查配置变化的间隔时间
            'interval' => 5,
        ]);
    }

    /**
     * 获取配置
     * @param string $key 配置key
     * @param mixed|null $default 默认值
     * @return mixed 配置值
     */
    public function get(string $key, mixed $default = null): mixed
    {
        /** @var Config $data */
        $data = $this->getModel()->where('name', $key)->first(['value']);

        return $data ? $data->value : $default;
    }

    /**
     * 获取所有配置
     * @return array
     */
    public function all(): array
    {
        $data = $this->getModel()->all(['name', 'value']);

        return $data->pluck('value', 'name')->toArray();
    }

    /**
     * 删除配置
     * @param array|string $keys 配置key
     * @return int
     */
    public function delete(array|string $keys): int
    {
        $keys = is_array($keys) ? $keys : [$keys];

        // 不允许删除配置版本号
        if (in_array($this->versionKey, $keys)) {
            throw new InvalidArgumentException('Can not delete "' . $this->versionKey . '" key');
        }

        return $this->getModel()->destroy($keys);
    }

    /**
     * 更新配置
     * @param string|array $key
     * @param mixed|null $value
     * @return void
     * @throws Throwable
     */
    public function set(string|array $key, mixed $value = null): void
    {
        if (is_array($key)) {
            $this->update($key);
            return;
        }

        $this->update([$key => $value]);
    }

    /**
     * 更新配置
     * @param array $data 配置数据 ['key' => 'value']
     * @return void
     * @throws Throwable
     */
    protected function update(array $data): void
    {
        $model = $this->getModel();

        $connection = $model->getConnection();
        $connection->beginTransaction();

        try {
            foreach ($data as $key => $value) {
                if ($key === $this->versionKey) {
                    throw new InvalidArgumentException('Can not update "' . $this->versionKey . '" key');
                }
                $model->updateOrCreate(['name' => $key], ['value' => $value]);
            }

            $version = intval(microtime(true) * 1000000);
            $model->updateOrCreate(['name' => $this->versionKey], ['value' => $version]);

            $connection->commit();
        } catch (Throwable $e) {
            $connection->rollBack();
            $this->logger->error('Failed to update config.', [
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * 获取配置版本号
     * @return string
     */
    public function getVersion(): string
    {
        return (string)$this->get($this->versionKey);
    }

    /**
     * 获取配置模型
     * @return Model
     */
    protected function getModel(): Model
    {
        return new $this->config['model'];
    }

    /**
     * 获取检查配置变化的间隔时间
     * @return int
     */
    public function getInterval(): int
    {
        return $this->config['interval'] ?? 5;
    }

    /**
     * 获取保存配置的文件路径
     * @return string
     */
    public function getConfigFilePath(): string
    {
        $filename = $this->getConfigFileName() . '.php';
        return BASE_PATH . '/config/autoload/' . $filename;
    }

    /**
     * 获取保存配置的文件名
     * @return string
     */
    public function getConfigFileName(): string
    {
        return $this->config['config_file'] ?? 'setting';
    }

    /**
     * 同步配置到文件
     * @return array
     */
    public function syncConfigToFile(): array
    {
        $config = $this->all();
        $filename = $this->getConfigFilePath();

        $data = '<?php return ' . var_export($config, true) . ';' . PHP_EOL;

        $result = file_put_contents($filename, $data);

        if ($result === false) {
            throw new RuntimeException('Failed to write config file to ' . $filename . '.');
        }

        return $config;
    }
}
