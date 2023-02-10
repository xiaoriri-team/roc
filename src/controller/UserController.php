<?php
/**
 * @author 小日日
 * @time 2023/2/9
 */

namespace roc\controller;

use roc\cache\CacheInterface;
use roc\Context;

class UserController {

    public function index(Context $context, CacheInterface $cache) {
        $cache->set(111, '9999');
        $context->writeJson([
            'user' => 'index',
            'cache' => $cache->get(111)
        ]);
    }
}