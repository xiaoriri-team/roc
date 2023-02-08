<?php
/**
 * @author 小日日
 * @time 2023/1/27
 */

namespace roc;

use BadMethodCallException;
use ReflectionClass;
use ReflectionException;

class Container
{
    protected static array $_singleton = [];


    /**
     * 绑定class关系
     * @param $class_name
     * @param $class_tow
     * @param array $params
     * @return void
     */
    public static function bind($class_name, $class_tow, array $params = [])
    {
        self::unbind($class_name);
        self::$_singleton[$class_name] = self::getInstance($class_tow, $params);
    }


    /**
     *  获取一个单例实例
     * @param $class_name
     * @return mixed|null
     */
    public static function getSingleton($class_name)
    {
        return array_key_exists($class_name, self::$_singleton) ?
            self::$_singleton[$class_name] : null;
    }

    /**
     * 解绑class关系
     * @param $class_name
     * @return void
     */
    public static function unbind($class_name)
    {
        self::$_singleton[$class_name] = null;
    }

    /**
     * 获取一个实例
     * @param string $class_name
     * @param array $params
     * @return mixed|object|null
     */
    public static function getInstance(string $class_name, array $params = [])
    {
        if (array_key_exists($class_name, self::$_singleton)) {
            return self::$_singleton[$class_name];
        }
        // 获取反射实例
        $reflector = new ReflectionClass($class_name);

        // 获取反射实例的构造方法
        $constructor = $reflector->getConstructor();
        // 获取反射实例构造方法的形参
        $di_params = [];
        if ($constructor) {
            foreach ($constructor->getParameters() as $param) {
                $class = $param->getClass();
                if ($class) {
                    // 如果依赖是单例，则直接获取
                    $singleton = self::getSingleton($class->name);
                    $di_params[] = $singleton ? $singleton : self::getInstance($class->name);
                }
            }
        }

        $di_params = array_merge($di_params, $params);

        // 创建实例
        $instance =  $reflector->newInstanceArgs($di_params);
        self::$_singleton[$class_name] = $instance;
        return $instance;
    }


    /**
     * 运行一个方法
     * @param string $class_name
     * @param string $method
     * @param array $params
     * @param array $construct_params
     * @return mixed
     * @throws ReflectionException
     */
    public static function run(string $class_name, string $method, array $params = [], array $construct_params = [])
    {
        if (!class_exists($class_name)) {
            throw new BadMethodCallException("Class $class_name is not found!");
        }

        if (!method_exists($class_name, $method)) {
            throw new BadMethodCallException("undefined method $method in $class_name !");
        }
        // 获取实例
        $instance = self::getInstance($class_name, $construct_params);

        // 获取反射实例
        $reflector = new ReflectionClass($class_name);
        // 获取方法
        $reflectorMethod = $reflector->getMethod($method);
        // 查找方法的参数
        $di_params = [];
        foreach ($reflectorMethod->getParameters() as $param) {
            $class = $param->getClass();
            if ($class) {
                $singleton = self::getSingleton($class->name);
                $di_params[] = $singleton ?: self::getInstance($class->name);
            }
        }

        // 运行方法
        return call_user_func_array([$instance, $method], array_merge($di_params, $params));
    }
}