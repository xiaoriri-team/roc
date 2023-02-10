<?php
/**
 * @author 小日日
 * @time 2023/2/10
 */

namespace roc\Pool;

class PoolOption
{

    /**
     * 连接池最小连接数
     * 获取连接时，如果连接池中的连接数小于该值，则创建新的连接
     * @var int
     */
    private int $minConnections = 1;

    /**
     * 连接池最大连接数
     * @var int
     */
    private int $maxConnections = 10;

    /**
     * 连接超时时间（秒）
     * 默认10秒
     *
     * @var float
     */
    private float $connectTimeout = 10.0;

    /**
     * 获取连接超时时间（秒）
     * 默认3秒
     *
     * @var float
     */
    private float $waitTimeout = 3.0;

    /**
     *连接心跳检测时间（秒）
     *
     * -1：不需要心跳检测
     *
     * @var float
     */
    private float $heartbeat = -1;

    /**
     * 连接的最大空闲时间（秒）
     * @var float
     */
    private float $maxIdleTime = 60.0;

    public function __construct(
        int $minConnections,
        int $maxConnections,
        float $connectTimeout,
        float $waitTimeout,
        float $heartbeat,
        float $maxIdleTime
    ) {
        $this->minConnections = $minConnections;
        $this->maxConnections = $maxConnections;
        $this->connectTimeout = $connectTimeout;
        $this->waitTimeout = $waitTimeout;
        $this->heartbeat = $heartbeat;
        $this->maxIdleTime = $maxIdleTime;
    }

    public function getMaxConnections(): int
    {
        return $this->maxConnections;
    }

    public function setMaxConnections(int $maxConnections): self
    {
        $this->maxConnections = $maxConnections;
        return $this;
    }

    public function getMinConnections(): int
    {
        return $this->minConnections;
    }

    public function setMinConnections(int $minConnections): self
    {
        $this->minConnections = $minConnections;
        return $this;
    }

    public function getConnectTimeout(): float
    {
        return $this->connectTimeout;
    }

    public function setConnectTimeout(float $connectTimeout): self
    {
        $this->connectTimeout = $connectTimeout;
        return $this;
    }

    public function getHeartbeat(): float
    {
        return $this->heartbeat;
    }

    public function setHeartbeat(float $heartbeat): self
    {
        $this->heartbeat = $heartbeat;
        return $this;
    }

    public function getWaitTimeout(): float
    {
        return $this->waitTimeout;
    }

    public function setWaitTimeout(float $waitTimeout): self
    {
        $this->waitTimeout = $waitTimeout;
        return $this;
    }

    public function getMaxIdleTime(): float
    {
        return $this->maxIdleTime;
    }

    public function setMaxIdleTime(float $maxIdleTime): self
    {
        $this->maxIdleTime = $maxIdleTime;
        return $this;
    }
}