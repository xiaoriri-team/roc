<?php
/**
 * @author 小日日
 * @time 2023/1/24
 */

namespace roc\Router;

interface IRoutes
{
    public function get(string $path, $callback): void;

    public function post(string $path, $callback): void;

    public function put(string $path, $callback): void;

    public function patch(string $path, $callback): void;

    public function delete(string $path, $callback): void;

    public function head(string $path, $callback): void;

    public function addRoute(string $method, string $path, $callback): void;

    public function addGroup(string $prefix, callable $callback): void;

    public function getData(string $method, string $path) :array;
}