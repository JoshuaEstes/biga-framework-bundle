<?php

/**
 * This file is part of the Biga Framework Bundle
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace BigaFrameworkBundle\Tests\Command\Hosts;

use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use BigaFrameworkBundle\Command\Hosts\CreateCommand;

/**
 * Test to make sure the hosts:create command works
 * as expected
 *
 * @author Joshua Estes
 */
class CreateCommandTest extends \PHPUnit_Framework_TestCase
{

    private $root;

    public function setUp()
    {
        $this->root = vfsStream::setup('etc', null, array('hosts' => ''));
    }

    public function testExecute()
    {
        $application = new Application();
        $application->add(new CreateCommand());

        $command = $application->find('hosts:create');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'          => 'hosts:create',
            '--hosts_file'     => vfsStream::url('etc/hosts'),
            '--host'           => 'biga.local',
        ), array(
            'interactive' => false
        ));
        $contents = $this->root->getChild('hosts')->getContent();
        $this->assertEquals('127.0.0.1 biga.local', $contents);
    }
}
