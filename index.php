<?php
/**
 * @author 小日日
 * @time 2023/1/4
 */

use roc\Application;
use roc\Container;

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

/**
 * @var Application $app
 */
$app = Container::pull(Application::class);
$app->run();

