<?php

namespace BigaFrameworkBundle\Command\Init;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('init:project')
            ->setDescription('Initialize a project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }

}
