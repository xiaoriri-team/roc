<?php
/**
 * @author 小日日
 * @time 2023/2/10
 */

namespace roc\Pool;

use SplQueue;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel as CoChannel;

/**
 * 通道类兼容协程和非协程
 * Class Channel
 * @package roc\Pool
 */
class Channel
{
    protected int $size;

    protected CoChannel $channel;

    protected SplQueue $queue;

    /**
     * @param int $size 通道容量
     */
    public function __construct(int $size)
    {
        $this->size = $size;
        $this->channel = new CoChannel($size);
        $this->queue = new SplQueue();
    }

    /**
     * 添加数据到通道
     * @param mixed $data
     * @return bool
     */
    public function push($data): bool
    {
        if ($this->isCoroutine()) {
            return $this->channel->push($data);
        }
        $this->queue->push($data);
        return true;
    }

    /**
     * 从通道中取出数据
     * @param float $timeout
     * @return mixed
     */
    public function pop(float $timeout)
    {
        if ($this->isCoroutine()) {
            return $this->channel->pop($timeout);
        }
        return $this->queue->shift();
    }

    /**
     * 获取通道中数据的数量
     * @return int
     */
    public function length(): int
    {
        if ($this->isCoroutine()) {
            return $this->channel->length();
        }
        return $this->queue->count();
    }


    /**
     * 判断是否是协程
     * @return bool
     */
    protected function isCoroutine(): bool
    {
        return Coroutine::getCid() > 0;
    }
}