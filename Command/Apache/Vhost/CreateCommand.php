<?php

namespace BigaFrameworkBundle\Command\Apache\Vhost;

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
            ->setName('apache:vhost:create')
            ->setDescription('Create an apache vhost file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }

}
