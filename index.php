<?php
/**
 * @author 小日日
 * @time 2023/1/4
 */

use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use roc\CorsMiddleware;
use roc\IRoutes;
use roc\MetricMiddleware;
use roc\RocServer;
use roc\Context;

require_once __DIR__ . '/vendor/autoload.php';

$routes = new \roc\TrieRoutes();
$routes->addRoute('GET', '/hello/:id/:name', function (Context $context) {
    $context->writeJson(['code' => 200, 'msg' => '小日日']);
});
$routes->addRoute('GET', '/word/ab', function (Context $context) {
    $context->writeJson(['code' => 200, 'msg' => '小日日']);
});
$routes->addRoute('GET', '/word/abc', function (Context $context) {
    $context->writeJson(['code' => 200, 'msg' => '小日日']);
});

$result = $routes->getRoute('GET', '/hello/abc/xiaoriri');
var_dump($result);
die;
exit;


$server = new RocServer('127.0.0.1', 9501, [
    new MetricMiddleware(),
    new CorsMiddleware()
]);
$server->get('/hello', function (Context $context) {
    $context->writeJson(['code' => 200, 'msg' => '小日日']);
});

$server->get('/', function (Context $context) {
    $context->writeJson(['data' => 'index']);
});
$server->start();

//$definitionSource = new DefinitionSource([IRoutes::class => Routes::class]);
//$container = new Container($definitionSource);
//$routers = $container->get(IRoutes::class);
//$routers->get("aaa", function () {
//    return "aaa";
//});


