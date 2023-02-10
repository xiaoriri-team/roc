<?php

namespace roc\watch;

use roc\Application;
use roc\cache\Cache;
use Swoole\Process;
use Swoole\Server;
use Swoole\Timer;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class WatchFile {
    public Server $server;
    public Application $app;
    public Cache $cache;
    public array $config;

    public function __construct() {
        $this->app = new Application();
        $this->cache = new Cache();
        $this->config = $this->app->getConfig('watch');
    }

    public function start(): void {
        Timer::tick($this->config['scan_interval'], function () {
            $file_cache = $this->cache->get('file_cache');
            if (!$file_cache) {
                echo '第一次启动' . PHP_EOL;
                $this->getWatchMD5();
                return;
            }

            $now_content = $this->getWatchMD5();
            if (md5(json_encode($now_content)) == md5(json_encode($file_cache))) {
                echo '没有变化' . PHP_EOL;
                return;
            }
            echo '有变化' . PHP_EOL;
            $path = BASE_PATH . DIRECTORY_SEPARATOR . 'server.pid';
            if (file_exists($path)) {
                $pid = file_get_contents($path);
                if (Process::kill((int)$pid, 0)) {
                    echo '重启服务' . PHP_EOL;
                    Process::kill((int)$pid, SIGTERM);
                    self::restart();
                }
            }
        });
    }

    /**
     * Get all of the files from the given directory (recursive).222
     * @return SplFileInfo[]
     */
    public function allFiles(string $directory, bool $hidden = false): array {
        return iterator_to_array(
            Finder::create()->files()->ignoreDotFiles(!$hidden)->in($directory)->sortByName(),
            false
        );
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param string $haystack
     * @param array|string $needles
     * @return bool
     */
    private function endsWith(string $haystack, $needles): bool {
        foreach ((array)$needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string)$needle) {
                return true;
            }
        }
        return false;
    }

    private function getWatchMD5(): array {
        $filesObj = [];
        $dir = $this->config['dir'];
        $ext = $this->config['ext'];
        foreach ($dir as $d) {
            $filesObj = array_merge($filesObj, $this->allFiles(BASE_PATH . '/' . $d));
        }
        $filesMD5 = [];
        /** @var SplFileInfo $obj */
        foreach ($filesObj as $obj) {
            $pathName = $obj->getPathName();
            if ($this->endsWith($pathName, $ext)) {
                $contents = file_get_contents($pathName);
                $filesMD5[$pathName] = md5($contents);
            }
        }
        $this->cache->set('file_cache', $filesMD5);
        return $filesMD5;
    }

    public static function restart() {
        //0 输入 1 输出 2 错误
        $descriptorspec = [0 => STDIN, 1 => STDOUT, 2 => STDERR,];
        $cmd = 'php ' . BASE_PATH . DIRECTORY_SEPARATOR . 'index.php start';
        $pipes = [];
        proc_open($cmd, $descriptorspec, $pipes, BASE_PATH);
    }
}