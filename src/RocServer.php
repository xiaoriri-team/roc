<?php
/**
 * @author 小日日
 * @time 2023/1/23
 */

namespace roc;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

class RocServer implements IRoutes
{
    private Server $server;
    private string $host;
    private int $port;
    private array $routes = [];

    /**
     * @var array<MiddlewareInterface>
     */
    private array $middlewares = [];

    public function __construct(string $host, int $port, $middlewares = [])
    {
        $this->host = $host;
        $this->port = $port;
        $this->middlewares = $middlewares;
        $this->server = new Server($this->host, $this->port);
    }

    public function start(): void
    {
        $this->server->on('request', function (Request $request, Response $response) {
            $handler = $this->findMatch($request->getMethod(), $request->server['request_uri']);
            $context = new Context();
            $context->setRequest($request);
            $context->setResponse($response);
            $root = $handler;
            foreach ($this->middlewares as $middleware) {
                $root = $middleware->handle($root);
            }
            $root($context);
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

    public function post(string $path, $callback): void
    {
        $this->setRoute('POST', $path, $callback);
    }

    public function put(string $path, $callback): void
    {
        $this->setRoute('PUT', $path, $callback);
    }

    public function patch(string $path, $callback): void
    {
        $this->setRoute('PATCH', $path, $callback);
    }

    public function delete(string $path, $callback): void
    {
        $this->setRoute('DELETE', $path, $callback);
    }

    public function head(string $path, $callback): void
    {
        $this->setRoute('HEAD', $path, $callback);
    }

    public function addRoute(string $method, string $path, $callback): void
    {
        $this->setRoute($method, $path, $callback);
    }

    public function addGroup(string $prefix, callable $callback): void
    {
        // TODO: Implement addGroup() method.
    }

    public function get(string $path, $callback): void
    {
        $this->setRoute('GET', $path, $callback);
    }
}