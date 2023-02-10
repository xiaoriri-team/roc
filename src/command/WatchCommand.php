<?php
/**
 * @author 小日日
 * @time 2023/2/10
 */

namespace roc\command;

use roc\watch\WatchFile;
use Swoole\Process;

class WatchCommand extends Command
{

    public function __construct()
    {
        parent::__construct('watch');
        $this->setDescription('Start roc servers.');
    }

    public function handle()
    {
        $process = new Process(function (){
            $file = new WatchFile();
            $file->start();
        });
        $process->start();
    }
}