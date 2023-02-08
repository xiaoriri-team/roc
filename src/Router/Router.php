<?php
/**
 * @author 小日日
 * @time 2023/2/7
 */

namespace roc\Router;

use roc\Container;

/**
 * @method static void addRoute($httpMethod, string $route, $handler)
 * @method static void addGroup($prefix, callable $callback)
 * @method static void get($route, $handler)
 * @method static void post($route, $handler)
 * @method static void put($route, $handler)
 * @method static void delete($route, $handler)
 * @method static void patch($route, $handler)
 * @method static void head($route, $handler)
 */
class Router
{
    public static function __callStatic($name, $arguments)
    {
        $router = Container::pull(IRoutes::class);
        return $router->{$name}(...$arguments);
    }
}