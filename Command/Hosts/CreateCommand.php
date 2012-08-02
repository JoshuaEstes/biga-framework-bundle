<?php

namespace BigaFrameworkBundle\Command\Hosts;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('hosts:create')
            ->setDescription('Add an entry in your hosts file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }

}
