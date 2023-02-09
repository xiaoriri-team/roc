<?php
namespace roc;

class Option {
    /**
     * @var string[]
     */
    protected array $watchDir = ['src', 'config'];

    /**
     * @var string[]
     */
    protected array $ext = ['.php'];

    /**
     * @var int
     */
    protected int $scanInterval = 2000;

    public function __construct($config, ?array $dir = []) {
        isset($config['dir']) && $this->watchDir = (array)$config['dir'];
        isset($config['scan_interval']) && $this->scanInterval = (int)$config['scan_interval'];
        isset($config['ext']) && $this->ext = (array)$config['ext'];
        if ($dir) {
            $this->watchDir = array_unique(array_merge($this->watchDir, $dir));
        }
    }

    public function getWatchDir(): array {
        return $this->watchDir;
    }

    public function getExt(): array {
        return $this->ext;
    }

    public function getScanInterval(): int {
        return $this->scanInterval > 0 ? $this->scanInterval : 2000;
    }
}
