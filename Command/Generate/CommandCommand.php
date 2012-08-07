<?php

/**
 * This file is part of the Biga Framework Bundle
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace BigaFrameworkBundle\Command\Generate;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a command
 *
 * @author Joshua Estes
 */
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
