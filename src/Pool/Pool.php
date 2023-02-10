<?php
/**
 * @author 小日日
 * @time 2023/2/10
 */

namespace roc\Pool;

use roc\Container;
use RuntimeException;
use Throwable;

abstract class Pool
{
    /**
     * @var Channel 通道
     */
    protected Channel $channel;

    /**
     * @var PoolOption 连接池配置
     */
    protected PoolOption $option;

    /**
     * @var int 当前连接数
     */
    protected int $currentConnections = 0;

    public function __construct(array $config = [])
    {
        $this->initOption($config);
        $this->channel = Container::getInstance()->make(
            Channel::class,
            ['size' => $this->option->getMaxConnections()],
            true
        );
    }

    public function getCurrentConnections(): int
    {
        return $this->currentConnections;
    }

    public function getOption(): PoolOption
    {
        return $this->option;
    }

    /**
     * 获取连接池中的连接数
     * @return int
     */
    public function getConnectionsInChannel(): int
    {
        return $this->channel->length();
    }

    /**
     * @return Connection
     * @throws Throwable
     */
    public function get(): Connection
    {
        return $this->getConnection();
    }


    public function release(Connection $connection): void
    {
        $this->channel->push($connection);
    }

    private function initOption(array $options = [])
    {
        $this->option = Container::getInstance()->make(
            PoolOption::class,
            [
                'minConnections' => $options['min_connections'] ?? 1,
                'maxConnections' => $options['max_connections'] ?? 10,
                'connectTimeout' => $options['connect_timeout'] ?? 10.0,
                'waitTimeout' => $options['wait_timeout'] ?? 3.0,
                'heartbeat' => $options['heartbeat'] ?? -1,
                'maxIdleTime' => $options['max_idle_time'] ?? 60.0,
            ]
        );
    }

    /**
     * 关闭连接池所有连接
     * @return void
     */
    public function flush(): void
    {
        $num = $this->getConnectionsInChannel();

        if ($num > 0) {
            while ($this->currentConnections > $this->option->getMinConnections() && $conn = $this->channel->pop(0.001)) {
                try {
                    $conn->close();
                } catch (\Throwable $exception) {
                    //todo 日志替换
                    echo (string)$exception;
                } finally {
                    --$this->currentConnections;
                    --$num;
                }

                if ($num <= 0) {
                    // Ignore connections queued during flushing.
                    break;
                }
            }
        }
    }

    /**
     * 关闭连接池一个连接
     * @param bool $must
     * @return void
     */
    public function flushOne(bool $must = false): void
    {
        $num = $this->getConnectionsInChannel();
        if ($num > 0 && $conn = $this->channel->pop(0.001)) {
            if ($must || !$conn->check()) {
                try {
                    $conn->close();
                } catch (\Throwable $exception) {
                    //todo 日志替换
                    echo (string)$exception;
                } finally {
                    --$this->currentConnections;
                }
            } else {
                $this->release($conn);
            }
        }
    }

    abstract protected function createConnection(): Connection;

    /**
     * 获取连接
     * @return Connection
     * @throws Throwable
     */
    private function getConnection(): Connection
    {
        $num = $this->getConnectionsInChannel();

        try {
            // 如果通道中没有连接，且当前连接数小于最大连接数，则创建一个新连接
            if ($num === 0 && $this->currentConnections < $this->option->getMaxConnections()) {
                ++$this->currentConnections;
                return $this->createConnection();
            }
        } catch (Throwable $throwable) {
            --$this->currentConnections;
            throw $throwable;
        }

        $connection = $this->channel->pop($this->option->getWaitTimeout());
        if (!$connection instanceof Connection) {
            throw new RuntimeException('Connection pool exhausted. Cannot establish new connection before wait_timeout.');
        }
        return $connection;
    }
}