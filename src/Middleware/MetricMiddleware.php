<?php

namespace roc\Middleware;

use roc\Context;

/**
 * @author 小日日
 * @time 2023/1/23
 */
class MetricMiddleware implements MiddlewareInterface
{

    public function handle(callable $next): callable
    {
        return function (Context $context) use ($next) {
            $start = microtime(true);
            $next($context);
            $end = microtime(true);
            echo "time: " . ($end - $start) . PHP_EOL;
        };
    }
}