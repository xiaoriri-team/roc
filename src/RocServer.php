<?php
/**
 * @author 小日日
 * @time 2023/1/23
 */

namespace roc;

use Closure;
use roc\Middleware\MiddlewareInterface;
use roc\Router\IRoutes;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

class RocServer
{
    private Server $server;
    private string $host;
    private int $port;
    private IRoutes $router;

    /**
     * @var array<MiddlewareInterface>
     */
    private array $middlewares;

    /**
     * @param array $middlewares
     */
    public function __construct(array $middlewares = [])
    {
        /**
         * @var Application $app
         */
        $app = Container::pull(Application::class);
        $config = $app->getConfig()['config']['server'];
        $this->host = $config['host'] ?? '0.0.0.0';
        $this->port = $config['port'] ?? 9501;
        $this->middlewares = $middlewares;
        $this->server = new Server($this->host, $this->port);
        $this->server->set(array(
            'pid_file' => BASE_PATH . '/server.pid',
            'worker_num' => 1
        ));
        $this->router = Container::pull(IRoutes::class);
    }

    public function start(): void
    {
        echo "Server is running at http://{$this->host}:{$this->port}" . PHP_EOL;
        $this->server->on('request', function (Request $request, Response $response) {
            $handler = $this->finMatch($request->getMethod(), $request->server['request_uri']);
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
     * 查找路由
     * @param $method
     * @param $path
     * @return Closure
     */
    private function finMatch($method, $path): Closure
    {
        [$routeItem, $args, $handler] = $this->router->getData($method, $path);
        //没有找到路由
        if ($routeItem === null) {
            $handler = function (Context $context) {
                $context->write(404, 'Not Found');
            };
        }
        //处理控制器方法调用
        if (is_array($handler)) {
            [$class, $action] = $handler;

            $handler = function (Context $context) use ($class, $action, $args) {
                if ($args) {
                    array_unshift($args, $context);
                } else {
                    $args = [$context];
                }
                Container::getInstance()->invokeClassFunction($class, $action, $args);
            };
        }
        //处理闭包函数调用
        if ($handler instanceof Closure) {
            $handler = function (Context $context) use ($handler, $args) {
                if ($args) {
                    array_unshift($args, $context);
                } else {
                    $args = [$context];
                }
                Container::getInstance()->invokeFunction($handler, $args);
            };
        }
        return $handler;
    }

}