<?php
/**
 * @author 小日日
 * @time 2023/1/27
 */

namespace roc;

use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Di\Container as DiContainer;
use Hyperf\Di\Definition\DefinitionSourceInterface;

class Container
{
    private static DefinitionSource $definitionSource;
    private static DiContainer $container;

    private static ?Container $instance = null;
    private static array $default = [
        IRoutes::class => TrieRoutes::class
    ];

    // 禁止clone
    private function __clone()
    {
    }

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
            self::$definitionSource = new DefinitionSource(self::$default);
            self::$container = new DiContainer(self::$definitionSource);
        }
        return self::$instance;
    }


    public static function make(string $name, array $parameters = [])
    {
        return self::getInstance()::$container->make($name, $parameters);
    }

    public static function set(string $name, $entry)
    {
        self::getInstance()::$container->set($name, $entry);
    }

    public static function unbind(string $name)
    {
        self::getInstance()::$container->unbind($name);
    }

    public static function define(string $name, $definition)
    {
        self::getInstance()::$container->define($name, $definition);
    }

    public static function get($name)
    {
        return self::getInstance()::$container->get($name);
    }

    public static function has($name): bool
    {
        return self::getInstance()::$container->has($name);
    }

    public static function getDefinitionSource(): DefinitionSourceInterface
    {
        return self::getInstance()::$container->getDefinitionSource();
    }

}