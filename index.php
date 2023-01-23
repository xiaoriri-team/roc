<?php
/**
 * @author 小日日
 * @time 2023/1/4
 */

use roc\MetricMiddleware;
use roc\RocServer;
use roc\Context;

require_once __DIR__ . '/vendor/autoload.php';

$server = new RocServer('127.0.0.1', 9501, [
    new MetricMiddleware()
]);
$server->setRoute('GET', '/hello', function (Context $context) {
    $context->writeJson(['code' => 200, 'msg' => '小日日']);
});

$server->setRoute('GET', '/', function (Context $context) {
    $context->writeJson(['data' => 'index']);
});
$server->start();