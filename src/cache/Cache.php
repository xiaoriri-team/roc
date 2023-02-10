<?php

namespace roc\cache;


class Cache {

    /**
     * 取得变量的存储文件名
     * @param        $name
     * @param string $dirName
     * @return string
     */
    protected function getCacheFile($name, $dirName = "")
    {
        if ($dirName) {
            $dirName .= DIRECTORY_SEPARATOR;
        }
        $name = md5($name . '12' . $name);
        $filename = 'cache_temp/' . $dirName . $name . '.php';
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $filename;
    }

    /**
     * 设置缓存
     * @param $name
     * @param $value
     * @param $expire
     * @return bool
     */
    public function set($name, $value, $expire = 0)
    {
        if (is_null($expire)) {
            $expire = 3600;
        }
        $filename = $this->getCacheFile($name);

        $data = serialize($value);
        $data = "<?php\n//" . sprintf('%012d', $expire) . $data . "\n?>";
        $result = file_put_contents($filename, $data);
        if ($result) {
            clearstatcache();
            return true;
        } else {
            return false;
        }
    }

    public function get($name, $default = null)
    {
        $filename = $this->getCacheFile($name);
        return $this->loadFromFile($filename, $default);
    }

    private function loadFromFile(string $filename, $default)
    {
        $content = false;
        if (is_file($filename)) {
            $content = file_get_contents($filename);
        }
        if (false === $content||strlen($content)<12) {
            return $default;
        }
        $expire = (int)substr($content, 8, 12);
        if (0 != $expire && time() > filemtime($filename) + $expire) {
            //缓存过期删除缓存文件
            $this->unlink($filename);
            return $default;
        }
        $content = substr($content, 20, -3);
        return unserialize($content);
    }

    /**
     * 判断文件是否存在后，删除
     *
     * @param $path
     *
     * @return bool
     * @return boolean
     * @author byron sampson <xiaobo.sun@qq.com>
     */
    private function unlink($path)
    {
        return is_file($path) && unlink($path);
    }
}