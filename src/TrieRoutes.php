<?php
/**
 * @author 小日日
 * @time 2023/1/24
 */

namespace roc;


/**
 * @description: 前缀路由树实现
 * @link https://weibo.com/ttarticle/p/show?id=2309404844027060289959&ivk_sa=32692
 */
class TrieRoutes implements IRoutes
{

    /**
     * @var array<TrieNode> $routes
     */
    private array $roots;
    /**
     * @var array<callable-string> $handlers
     */
    private array $handlers;


    public function addRoute(string $method, string $path, $callback): void
    {
        $parts = $this->parsePattern($path);
        $key = $method . "-" . $path;
        // 如果不存在该方法的根节点，则创建
        if (!isset($this->roots[$method])) {
            $this->roots[$method] = new TrieNode();
        }
        //插入路由
        $this->roots[$method]->insert($path, $parts, 0);
        //对应的方法放到map中
        $this->handlers[$key] = $callback;
    }

    public function getRoute(string $method, string $path)
    {
        $searchParts = $this->parsePattern($path);
        $params = [];
        $root = $this->roots[$method];
        if ($root === null) {
            return [null, null];
        }
        $n = $root->search($searchParts, 0);
        if ($n != null) {
            $parts = $this->parsePattern($n->pattern);
            foreach ($parts as $index => $part) {
                if ($part[0] == ':') {
                    $params[substr($part, 1)] = $searchParts[$index];
                }
                if ($part[0] == '*' && strlen($part) > 1) {
                    $params[substr($part, 1)] = implode('/', array_slice($searchParts, $index));
                    break;
                }
            }
            return [$n, $params];
        }
        return [null, null];
    }

    /**
     * 该方法就是将入参完整的URL用斜杠分隔成字符串数组
     * 只考虑一个*的情况
     * /a/b/c => ['a', 'b', 'c']
     * /a/b/* => ['a', 'b', '*']
     * /p/:lang/doc => ['p', ':lang', 'doc']
     * @param string $pattern 路由地址
     * @return array<string>
     */
    public function parsePattern(string $pattern): array
    {
        $vars = explode('/', $pattern);
        $parts = [];
        foreach ($vars as $part) {
            if ($part !== '') {
                $parts[] = $part;
                if ($part[0] == '*') {
                    break;
                }
            }
        }
        return $parts;
    }

    public function get(string $path, $callback): void
    {
        $this->addRoute('GET', $path, $callback);
    }

    public function post(string $path, $callback): void
    {
        $this->addRoute('POST', $path, $callback);
    }

    public function put(string $path, $callback): void
    {
        $this->addRoute('PUT', $path, $callback);
    }

    public function patch(string $path, $callback): void
    {
        $this->addRoute('PATCH', $path, $callback);
    }

    public function delete(string $path, $callback): void
    {
        $this->addRoute('DELETE', $path, $callback);
    }

    public function head(string $path, $callback): void
    {
        $this->addRoute('HEAD', $path, $callback);
    }

    public function addGroup(string $prefix, callable $callback): void
    {
        // TODO: Implement addGroup() method.
    }
}