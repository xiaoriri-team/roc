<?php
/**
 * @author 小日日
 * @time 2023/2/10
 */

namespace roc\command;


use roc\watch\WatchFile;


class WatchCommand extends Command
{

    public function __construct()
    {
        parent::__construct('watch');
        $this->setDescription('Start roc servers.');
    }

    public function handle()
    {
        $file = new WatchFile();
        $file->start();
        WatchFile::restart();
    }
}