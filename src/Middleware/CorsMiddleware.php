<?php
/**
 * @author 小日日
 * @time 2023/1/24
 */

namespace roc\Middleware;

use roc\Context;

class CorsMiddleware implements MiddlewareInterface
{

    public function handle(callable $next): callable
    {
        return function (Context $context) use ($next) {
            $response = $context->getResponse();
            $method = $context->getRequest()->getMethod();
            if ($method == 'OPTIONS') {
                $response->end();
            } else {
                $response->header('Access-Control-Allow-Origin', '*');
                $response->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
                $response->header('Access-Control-Allow-Headers',
                    'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authorization');
            }
            $next($context);
        };
    }
}