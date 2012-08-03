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
            ->setDescription('Initialize a project')
            ->setHelp(<<<EOF
This command only really needs to be run once. After that
everything should be setup. This command takes care of updating
your parameters.yml file, adding an entry into your hosts file,
and creating a vhost entry for you.
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {

    }

}
