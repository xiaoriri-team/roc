<?php
/**
 * @author 小日日
 * @time 2023/1/27
 */

namespace roc;

use Exception;
use roc\command\StartCommand;
use roc\command\WatchCommand;
use roc\Router\IRoutes;
use roc\Router\TrieRoutes;

class Application
{

    /**
     * @var array 默认配置文件
     */
    private array $configs = [
        'config' => [
            'server' => [
                'host' => '0.0.0.0',
                'port' => 9501
            ]
        ],
        'watch' => [
            'dir' => ['src', 'config'],
            'ext' => ['.php'],
            'scan_interval' => 2000,
        ]
    ];

    /**
     * 依赖注入绑定
     * @var array|string[]
     */
    protected array $bind = [
        IRoutes::class => TrieRoutes::class
    ];


    /**
     * @return void
     * @throws Exception
     */
    public function run()
    {
        $this->initContainer(); //优先级最高
        $this->initConfig();
        $this->initCommand();

    }

    /**
     * 加载配置文件
     * @return void
     */
    private function initConfig(): void
    {
        //扫描config目录下的配置文件
        $path = BASE_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
        if (is_dir($path)) {
            $files = scandir($path);
            $files = array_filter($files, function ($file) {
                return $file !== '.' && $file !== '..';
            });
            foreach ($files as $filename) {
                if (file_exists($path . $filename)) {
                    $key = substr($filename, 0, strrpos($filename, "."));
                    $result = require_once $path . $filename;
                    if ($result) {
                        $this->configs[$key] = $result;
                    }
                }
            }
        }
    }

    /**
     * 初始化容器
     * @return void
     */
    private function initContainer()
    {
        $path = BASE_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
        $result = require_once $path . 'provider.php';
        if ($result) {
            $this->configs['provider'] = $result;
        }
        $this->bind = array_merge($this->configs['provider'] ?? [],$this->bind);
        Container::getInstance()->bind($this->bind);
    }

    /**
     * 初始化命令行
     * @return void
     * @throws Exception
     */
    private function initCommand()
    {
        $application = new \Symfony\Component\Console\Application();
        $application->add(new StartCommand());
        $application->add(new WatchCommand());
        $application->run();
    }


    /**
     * 获取配置信息
     * @param string $module
     * @param $default
     * @return array|array[]
     */
    public function getConfig(string $module = '', $default = null): array
    {
        if ($module) {
            return $this->configs[$module] ?: $default;
        }
        return $this->configs;
    }
}