<?php
/**
 * @author 小日日
 * @time 2023/2/8
 */

use roc\Context;
use roc\Router\Router;
use roc\Test;

Router::get('/hello/:id', function (Context $context, $id) {
    $context->writeJson(['code' => 666, 'msg' => '很牛逼啊哈哈？', 'id' => $id]);
});

Router::get('/stop', function (Context $context) {
    $context->writeJson(['msg'=>'stop']);
    exit();
});

Router::get('/test', [Test::class, 'test']);
