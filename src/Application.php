<?php
/**
 * @author 小日日
 * @time 2023/1/27
 */

namespace roc;

use roc\command\StartCommand;
use roc\Router\IRoutes;
use roc\Router\TrieRoutes;

class Application
{
    private array $configs = [
        'config' => [
            'server' => [
                'host' => '0.0.0.0',
                'port' => 9501
            ]
        ]
    ];

    public  $server;

    /**
     * @return void
     * @throws \Exception
     */
    public function run()
    {
        $this->initContainer(); //优先级最高
        $this->initConfig();
        $this->initServer();
        $this->initCommand();
    }

    private function initConfig()
    {
        $path = BASE_PATH . DIRECTORY_SEPARATOR . 'config'. DIRECTORY_SEPARATOR;
        if (is_dir($path)) {
            $files = scandir($path);
            $files = array_filter($files, function ($file) {
                return $file !== '.' && $file !== '..';
            });

            foreach ($files as $filename) {
                if (file_exists($path .$filename)) {
                    $key = substr($filename, 0, strrpos($filename, "."));
                    $result = require_once $path .$filename;
                    if ($result) {
                        $this->configs[$key] = $result;
                    }
                }
            }
        }
    }

    private function initContainer()
    {
        Container::bind(IRoutes::class, TrieRoutes::class);
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function initCommand()
    {
        $application = new \Symfony\Component\Console\Application();
        $application->add(new StartCommand());
        $application->run();
    }


    private function initServer()
    {
        $config = $this->configs['config']['server'];
        $this->server = new RocServer($config['host'], $config['port']);
    }

    public function getServer(): RocServer
    {
        return $this->server;
    }

}