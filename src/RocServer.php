<?php
/**
 * @author 小日日
 * @time 2023/1/23
 */

namespace roc;

use Closure;
use Exception;
use http\Exception\BadMethodCallException;
use ReflectionClass;
use ReflectionException;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

class RocServer {
    private Server $server;
    private string $host;
    private int $port;
    private array $routes = [];
    private IRoutes $router;

    /**
     * @var array<MiddlewareInterface>
     */
    private array $middlewares = [];

    /**
     * @param string $host
     * @param int $port
     * @param array $middlewares
     */
    public function __construct(string $host, int $port,array $middlewares = []) {
        $this->host = $host;
        $this->port = $port;
        $this->middlewares = $middlewares;
        $this->server = new Server($this->host, $this->port);
        $this->router = Container::getInstance(IRoutes::class);
    }

    public function start(): void {
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

    /**
     * @param $method
     * @param $path
     * @return Closure
     * @throws ReflectionException
     * @throws Exception
     */
    private function findMatch($method, $path): Closure {
        $key = strtolower($method) . '#' . $path;
        if (isset($this->routes[$key])) {
            [$class, $action] = $this->routes[$key];
            $clazz = new ReflectionClass($class);
            if ($clazz->hasMethod($action)) {
                $tmp = $clazz->getMethod($action);
                if ($tmp->ispublic()) {
                    return function (Context $context) use ($tmp, $clazz) {
                        $context->writeJson($tmp->invoke($clazz->newInstance()));
                    };
                } else {
                    throw new BadMethodCallException("$clazz " .$action);
                }
            } else {
                throw new Exception("$clazz " .$action);
            }
        }
        return function (Context $context) {
            $context->write(404, 'Not Found');
        };
    }

    public function setRoute(string $method, string $path, array $callback): void {
        $key = strtolower($method) . '#' . $path;
        $this->routes[$key] = $callback;
    }
}