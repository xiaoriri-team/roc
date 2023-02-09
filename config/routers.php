<?php
/**
 * @author 小日日
 * @time 2023/2/8
 */

use roc\Context;
use roc\Router\Router;
use roc\Test;

Router::get('/hello', function (Context $context) {
    $context->writeJson(['code' => 200, 'msg' => '小日日12312']);
});

Router::get('/test', [Test::class, 'test']);
