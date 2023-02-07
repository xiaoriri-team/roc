<?php
/**
 * @author 小日日
 * @time 2023/1/4
 */

use roc\Context;
use roc\CorsMiddleware;
use roc\MetricMiddleware;
use roc\RocServer;
use roc\Test;

require_once __DIR__ . '/vendor/autoload.php';
//
//$routes = new \roc\TrieRoutes();
//$routes->addRoute('GET', '/hello/:id/:name', function (Context $context) {
//    $context->writeJson(['code' => 200, 'msg' => '小日日']);
//});
//$routes->addRoute('GET', '/word/ab', function (Context $context) {
//    $context->writeJson(['code' => 200, 'msg' => '小日日']);
//});
//$routes->addRoute('GET', '/word/abc', function (Context $context) {
//    $context->writeJson(['code' => 200, 'msg' => '小日日']);
//});
//
//$result = $routes->getRoute('GET', '/hello/abc/xiaoriri');
//var_dump($result);
//die;
//exit;


$server = new RocServer('0.0.0.0', 9501, [
    new MetricMiddleware(),
    new CorsMiddleware(),
]);
//$server->setRoute('get', '/hello', function (Context $context) {
//    $context->writeJson(['code' => 200, 'msg' => '小日日']);
//});
$server->setRoute('get', '/hello', [Test::class, 'test']);
$server->start();

//$definitionSource = new DefinitionSource([IRoutes::class => Routes::class]);
//$container = new Container($definitionSource);
//$routers = $container->get(IRoutes::class);
//$routers->get("aaa", function () {
//    return "aaa";
//});


