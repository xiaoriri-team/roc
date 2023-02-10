<?php
/**
 * @author 小日日
 * @time 2023/2/8
 */

use roc\Context;
use roc\Router\Router;
use roc\Test;

Router::get('/hello/:id', function (Context $context, $id) {
    $context->writeJson(['code' => 123123, 'msg' => 'aaaa？', 'id' => $id]);
});

Router::get('/stop', function (Context $context) {
    $context->writeJson(['msg'=>'stop']);
    exit();
});

Router::get('/test', [Test::class, 'test']);
