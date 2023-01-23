<?php
/**
 * @author 小日日
 * @time 2023/1/23
 */

namespace roc;

interface MiddlewareInterface
{
    /**
     * @param callable $next
     * @return callable
     */
    public function handle(callable $next): callable;
}