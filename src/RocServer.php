<?php
/**
 * @author 小日日
 * @time 2023/1/23
 */

namespace roc;

use roc\cache\Cache;
use roc\Middleware\MiddlewareInterface;
use roc\Router\IRoutes;
use roc\watch\WatchFile;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

class RocServer
{
    private Server $server;
    private string $host;
    private int $port;
    private IRoutes $router;
    private Cache $cache;

    /**
     * @var array<MiddlewareInterface>
     */
    private array $middlewares;

    /**
     * @param string $host
     * @param int $port
     * @param array $middlewares
     */
    public function __construct(string $host, int $port, array $middlewares = [])
    {
        $this->host = $host;
        $this->port = $port;
        $this->middlewares = $middlewares;
        $this->server = new Server($this->host, $this->port);
        $this->router = Container::pull(IRoutes::class);
        $this->cache = new Cache();
    }

    public function start(): void
    {
        echo "Server is running at http://{$this->host}:{$this->port}" . PHP_EOL;
        $this->server->on('request', function (Request $request, Response $response) {
            //todo 处理回调参数问题
            //todo 路由中间件实现
            [$routeItem, $args, $handler] = $this->router->getData(
                $request->getMethod(),
                $request->server['request_uri']
            );
            //没有找到路由
            if ($routeItem === null) {
                $handler = function (Context $context) {
                    $context->write(404, 'Not Found');
                };
            }
            //处理控制器方法调用
            if (is_array($handler)) {
                [$class, $action] = $handler;
                $handler = function (Context $context) use ($class, $action) {
                    $context->writeJson(
                        Container::getInstance()->invokeClassFunction($class, $action)
                    );
                };
            }
            $context = new Context();
            $context->setRequest($request);
            $context->setResponse($response);
            $root = $handler;
            foreach ($this->middlewares as $middleware) {
                $root = $middleware->handle($root);
            }
            $root($context);
        });
        $this->initWatchFile();
        $this->cache->set('is_start',1);
        $this->server->start();
    }

    private function initWatchFile(): void {
        $file = new WatchFile();
        $file->start();
    }
}