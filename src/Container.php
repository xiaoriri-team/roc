<?php
namespace roc;

use BadFunctionCallException;
use Closure;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;

/**
 * @author 小日日
 * @time 2023/1/27
 */
class Container
{

    /**
     * @var Container  容器对象实例
     */
    protected static  $instance;

    /**
     * 容器中的对象实例
     * @var array
     */
    protected array $instances = [];

    /**
     * 容器绑定标识
     * @var array
     */
    protected array $bind = [];

    /**
     * 容器回调
     * @var array
     */
    protected array $invokeCallback = [];


    /**
     * 获取当前容器的实例（单例）
     * @return static
     */
    public static function getInstance(): Container
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * 根据别名获取真实类名
     * @param string $abstract
     * @return string
     */
    public function getAlias(string $abstract): string
    {
        if (isset($this->bind[$abstract])) {
            $bind = $this->bind[$abstract];

            if (is_string($bind)) {
                return $this->getAlias($bind);
            }
        }

        return $abstract;
    }

    /**
     * 注册一个容器对象回调
     *
     * @param string|Closure $abstract
     * @param Closure|null $callback
     * @return void
     */
    public function resolving($abstract, Closure $callback = null): void
    {
        // 注册一个全局回调
        if ($abstract instanceof Closure) {
            $this->invokeCallback['*'][] = $abstract;
            return;
        }
        $abstract = $this->getAlias($abstract);

        $this->invokeCallback[$abstract][] = $callback;
    }

    /**
     * 获取容器中的对象实例 不存在则创建
     * @template T
     * @param string|class-string $abstract 类名或者标识
     * @param array $vars 变量
     * @param bool $newInstance 是否每次创建新的实例
     * @return T|object
     */
    public static function pull(string $abstract, array $vars = [], bool $newInstance = false)
    {
        return static::getInstance()->make($abstract, $vars, $newInstance);
    }

    /**
     * 获取容器中的对象实例
     * @template T
     * @param string|class-string<T> $abstract 类名或者标识
     * @return T|object
     */
    public function get(string $abstract)
    {
        if ($this->has($abstract)) {
            return $this->make($abstract);
        }
        throw new InvalidArgumentException('class not exists: ' . $abstract);
    }

    /**
     * 判断容器中是否存在类及标识
     * @param string $name 类名或者标识
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->bind[$name]) || isset($this->instances[$name]);
    }

    /**
     * 判断容器中是否存在对象实例
     * @param string $abstract 类名或者标识
     * @return bool
     */
    public function exists(string $abstract): bool
    {
        $abstract = $this->getAlias($abstract);

        return isset($this->instances[$abstract]);
    }


    /**
     * 删除容器中的对象实例
     * @param string $name 类名或者标识
     * @return void
     */
    public function delete(string $name): void
    {
        $name = $this->getAlias($name);

        if (isset($this->instances[$name])) {
            unset($this->instances[$name]);
        }
    }

    /**
     * 获取一个实例
     * @param string $abstract
     * @param array $vars
     * @param bool $newInstance
     * @return mixed|object|null
     */
    public function make(string $abstract, array $vars = [], bool $newInstance = false)
    {
        $abstract = $this->getAlias($abstract);

        if (isset($this->instances[$abstract]) && !$newInstance) {
            return $this->instances[$abstract];
        }

        if (isset($this->bind[$abstract]) && $this->bind[$abstract] instanceof Closure) {
            $object = $this->invokeFunction($this->bind[$abstract], $vars);
        } else {
            $object = $this->invokeClass($abstract, $vars);
        }

        if (!$newInstance) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }


    /**
     * 绑定一个类、闭包、实例、接口实现到容器
     * @param string|array $abstract 类标识、接口
     * @param mixed $concrete 要绑定的类、闭包或者实例
     * @return $this
     */
    public function bind($abstract, $concrete = null): Container
    {
        if (is_array($abstract)) {
            foreach ($abstract as $key => $val) {
                $this->bind($key, $val);
            }
        } elseif ($concrete instanceof Closure) {
            $this->bind[$abstract] = $concrete;
        } elseif (is_object($concrete)) {
            $this->instance($abstract, $concrete);
        } else {
            $abstract = $this->getAlias($abstract);
            if ($abstract != $concrete) {
                $this->bind[$abstract] = $concrete;
            }
        }

        return $this;
    }

    /**
     * 绑定一个类实例到容器
     * @param string $abstract 类名或者标识
     * @param object $instance 类的实例
     * @return $this
     */
    public function instance(string $abstract, object $instance): Container
    {
        $abstract = $this->getAlias($abstract);

        $this->instances[$abstract] = $instance;

        return $this;
    }

    /**
     * 调用反射执行类的实例化 支持依赖注入
     * @param string $class 类名
     * @param array $vars 参数
     * @return mixed
     */
    public function invokeClass(string $class, array $vars = [])
    {
        try {
            $reflect = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new InvalidArgumentException('class not exists: ' . $class);
        }

        $constructor = $reflect->getConstructor();

        $args = $constructor ? $this->bindParams($constructor, $vars) : [];

        $object = $reflect->newInstanceArgs($args);

        //调用绑定之后的回调函数
        $this->invokeAfter($class, $object);

        return $object;
    }

    /**
     * 执行invokeClass回调
     * @param string $class 对象类名
     * @param object $object 容器对象实例
     * @return void
     */
    protected function invokeAfter(string $class, object $object): void
    {
        if (isset($this->invokeCallback['*'])) {
            foreach ($this->invokeCallback['*'] as $callback) {
                $callback($object, $this);
            }
        }

        if (isset($this->invokeCallback[$class])) {
            foreach ($this->invokeCallback[$class] as $callback) {
                $callback($object, $this);
            }
        }
    }

    /**
     * 执行函数或者闭包方法 支持参数调用
     * @param string|Closure $function 函数或者闭包
     * @param array $vars 参数
     * @return mixed
     */
    public function invokeFunction($function, array $vars = [])
    {
        try {
            $reflect = new ReflectionFunction($function);
        } catch (ReflectionException $e) {
            throw new BadFunctionCallException("function not exists: {$function}()", $function);
        }

        $args = $this->bindParams($reflect, $vars);

        return $function(...$args);
    }

    /**
     * 绑定参数
     * @param ReflectionFunctionAbstract $reflect 反射类
     * @param array $vars 参数
     * @return array
     */
    protected function bindParams(ReflectionFunctionAbstract $reflect, array $vars = []): array
    {
        if ($reflect->getNumberOfParameters() == 0) {
            return [];
        }

        // 判断数组类型 数字数组时按顺序绑定参数
        reset($vars);
        $type = key($vars) === 0 ? 1 : 0;
        $params = $reflect->getParameters();
        $args = [];

        foreach ($params as $param) {
            $name = $param->getName();
            $reflectionType = $param->getType();

            if ($reflectionType && $reflectionType->isBuiltin() === false) {
                //判断不是内置的数据类型 如果当前形参数和传过来的参数一致则直接赋值 否则调用make实例化
                $args[] = $this->getObjectParam($reflectionType->getName(), $vars);
            } elseif (1 == $type && !empty($vars)) {
                // 数字数组
                $args[] = array_shift($vars);
            } elseif (0 == $type && array_key_exists($name, $vars)) {
                // 关联数组
                $args[] = $vars[$name];
            } elseif ($param->isDefaultValueAvailable()) {
                // 默认值
                $args[] = $param->getDefaultValue();
            } else {
                throw new InvalidArgumentException('method param miss:' . $name);
            }
        }

        return $args;
    }

    /**
     * 获取对象类型的参数值
     * @param string $className 类名
     * @param array $vars 参数
     * @return mixed
     */
    protected function getObjectParam(string $className, array &$vars)
    {
        $array = $vars;
        $value = array_shift($array);

        if ($value instanceof $className) {
            $result = $value;
            array_shift($vars);
        } else {
            $result = $this->make($className);
        }

        return $result;
    }

    /**
     * 执行类的方法 支持参数调用
     * @param string $className
     * @param string $method
     * @param array $methodVars
     * @param array $classVars
     * @return mixed
     * @throws ReflectionException
     */
    public function invokeClassFunction(
        string $className,
        string $method,
        array $methodVars = [],
        array $classVars = []
    ) {
        $className = $this->getAlias($className);
        // 获取实例
        $instance = $this->invokeClass($className, $classVars);

        //处理方法参数
        $reflector = new ReflectionClass($className);
        $reflectorMethod = $reflector->getMethod($method);
        $args = $this->bindParams($reflectorMethod, $methodVars);

        // 运行方法
        return call_user_func_array([$instance, $method], $args);

    }
}