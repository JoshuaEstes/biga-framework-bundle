<?php

namespace BigaFrameworkBundle\Command\Generate;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CommandCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('generate:command')
            ->setDescription('Generate a command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }

}
