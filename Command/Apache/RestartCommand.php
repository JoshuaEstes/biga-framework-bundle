<?php

namespace BigaFrameworkBundle\Command\Apache;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RestartCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('apache:restart')
            ->setDescription('Restart Apache web server');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }

}
