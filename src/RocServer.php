<?php
/**
 * @author 小日日
 * @time 2023/1/23
 */

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

class RocServer
{
    private Server $server;
    private string $host;
    private int $port;
    private array $routes = [];

    public function __construct(string $host, int $port)
    {
        $this->host = $host;
        $this->port = $port;
        $this->server = new Server($this->host, $this->port);
    }

    public function start(): void
    {
        $this->server->on('request', function (Request $request, Response $response) {
            $handler = $this->findMatch($request->getMethod(), $request->server['request_uri']);
            $context = new Context();
            $context->setRequest($request);
            $context->setResponse($response);
            $handler($context);
        });
        $this->server->start();

    }

    private function findMatch($method, $path): callable
    {
        $key = strtolower($method) . '#' . $path;
        if (isset($this->routes[$key])) {
            return $this->routes[$key];
        }
        return function (Context $context) {
            $context->write(404, 'Not Found');
        };
    }

    public function setRoute(string $method, string $path, callable $callback): void
    {
        $key = strtolower($method) . '#' . $path;
        $this->routes[$key] = $callback;
    }
}