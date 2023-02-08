<?php
/**
 * @author 小日日
 * @time 2023/2/8
 */

namespace roc\command;

use roc\Application;
use roc\Container;

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
         * @var Application $app
         */
        $app = Container::pull(Application::class);
        $server = $app->getServer();
        $server->start();
    }
}