<?php

namespace roc\cache;

interface CacheInterface {
    public function get($name, $default = null);
    public function set($name, $value, $expire = 0);
}