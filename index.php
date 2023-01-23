<?php
/**
 * @author 小日日
 * @time 2023/1/4
 */


require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Context.php';
require_once __DIR__ . '/RocServer.php';


$server = new RocServer('127.0.0.1', 9501);
$server->setRoute('GET', '/hello', function (Context $context) {
    $context->writeJson(['code' => 200, 'msg' => '小日日']);
});

$server->setRoute('GET', '/', function (Context $context) {
    $context->writeJson([ 'data' => 'index']);
});
$server->start();