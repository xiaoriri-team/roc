<?php
/**
 * @author 小日日
 * @time 2023/2/8
 */

namespace roc\command;

use roc\Container;
use roc\RocServer;

class StartCommand extends Command
{

    public function __construct()
    {
        parent::__construct('start');
        $this->setDescription('Start roc servers.');
    }

    public function handle()
    {
        /**
         * @var RocServer $server
         */
        $server = Container::pull(RocServer::class, ['host' => '0.0.0.0', 'port' => 9501]);
        $server->start();
    }
}