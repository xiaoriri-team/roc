<?php
/**
 * @author 小日日
 * @time 2023/2/10
 */

namespace roc\Pool;


abstract class Connection
{

    /**
     * @var Pool
     */
    protected Pool $pool;

    /**
     * @var float
     */
    protected float $lastUseTime = 0.0;

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    public function release(): void
    {
        $this->pool->release($this);
    }

    public function getConnection()
    {
        try {
            return $this->getActiveConnection();
        } catch (\Throwable $exception) {
            //todo 用log替换
            echo 'Get connection failed, try again. ' . (string)$exception;
            return $this->getActiveConnection();
        }
    }

    public function check(): bool
    {
        $maxIdleTime = $this->pool->getOption()->getMaxIdleTime();
        $now = microtime(true);
        if ($now > $maxIdleTime + $this->lastUseTime) {
            return false;
        }

        $this->lastUseTime = $now;
        return true;
    }

    abstract public function getActiveConnection();
}