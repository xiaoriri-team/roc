<?php
/**
 * @author 小日日
 * @time 2023/1/4
 */

use roc\Application;
use roc\Context;
use roc\Middleware\CorsMiddleware;
use roc\Middleware\MetricMiddleware;
use roc\RocServer;
use roc\Router\Router;


ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

$basePath = getcwd();
$dir = dirname(__DIR__, 3);
if (file_exists($dir . '/vendor/autoload.php')) {
    $basePath = $dir;
}
!defined('BASE_PATH') && define('BASE_PATH', $basePath);


require_once __DIR__ . '/vendor/autoload.php';
$app = \roc\Container::getInstance(Application::class);
$app->run();
//Router::get('/hello', function (Context $context) {
//    $context->writeJson(['code' => 200, 'msg' => '小日日12312']);
//});
//Router::get('/test', [\roc\Test::class, 'test']);
//$server = new RocServer('0.0.0.0', 9501, [
//    new CorsMiddleware(),
//    new MetricMiddleware(),
//]);
////$server->setRoute('get', '/hello', function (Context $context) {
////    $context->writeJson(['code' => 200, 'msg' => '小日日']);
////});
////$server->setRoute('get', '/hello', [Test::class, 'test']);
//$server->start();

//$definitionSource = new DefinitionSource([IRoutes::class => Routes::class]);
//$container = new Container($definitionSource);
//$routers = $container->get(IRoutes::class);
//$routers->get("aaa", function () {
//    return "aaa";
//});


