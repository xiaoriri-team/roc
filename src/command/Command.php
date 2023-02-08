<?php
/**
 * @author 小日日
 * @time 2023/2/8
 */

namespace roc\command;

use \Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends SymfonyCommand
{

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->handle();
            return 0;
        }catch (\Exception $e){
            $output->writeln($e->getMessage());
            return -1;
        }
    }
    abstract public function handle();

}