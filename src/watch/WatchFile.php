<?php

namespace roc\watch;

use roc\Application;
use roc\cache\Cache;
use Swoole\Server;
use Swoole\Timer;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class WatchFile {
    public Server $server;
    public Application $app;
    public Cache $cache;

    public function __construct() {
        $this->app = new Application();
        $this->cache = new Cache();
    }

    public function start(): void {
        Timer::tick(2000, function () {
            $is_start = $this->cache->get('is_start');
            $file_cache = $this->cache->get('file_cache');
            $now_content = $this->getWatchMD5();
            if (md5(json_encode($now_content)) == md5(json_encode($file_cache))) {
                return;
            }
            if ($is_start) {
                // TODO 重新启动
                // $this->app->server->start();
            }
        });
    }

    /**
     * Get all of the files from the given directory (recursive).222
     * @return SplFileInfo[]
     */
    public function allFiles(string $directory, bool $hidden = false): array
    {
        return iterator_to_array(
            Finder::create()->files()->ignoreDotFiles(! $hidden)->in($directory)->sortByName(),
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
    private function endsWith(string $haystack, array|string $needles): bool {
        foreach ((array) $needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string) $needle) {
                return true;
            }
        }
        return false;
    }

    private function getWatchMD5(): array {
        $filesObj = [];
        $dir = ['src', 'config'];
        $ext = ['.php'];
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
        $this->cache->set('file_cache',$filesMD5);
        return $filesMD5;
    }
}