<?php

/**
 * This file is part of the Biga Framework Bundle
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace BigaFrameworkBundle\Tests\Command\Apache\Vhost;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use BigaFrameworkBundle\Command\Apache\Vhost\CreateCommand;

/**
 * Test to make sure the hosts:create command works
 * as expected
 *
 * @author Joshua Estes
 */
class CreateCommandTest extends \PHPUnit_Framework_TestCase
{

    private $root;
    private $structure = array(
        'etc' => array(
            'apache2' => array('sites-enabled' => array()),
        ),
    );

    public function setUp()
    {
        $this->root = vfsStream::setup('root', null, $this->structure);
    }

    public function testExecute()
    {
        $application = new Application();
        $application->add(new CreateCommand());

        $command = $application->find('apache:vhost:create');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'               => 'apache:vhost:create',
            '--server_name'         => 'test.local',
            '--document_root'       => '/var/www/public_html',
            '--sites_available_dir' => vfsStream::url('root/etc/apache2/sites-enabled'),
        ), array(
            'interactive' => false
        ));
        $contents = $this->root
            ->getChild('etc')
            ->getChild('apache2')
            ->getChild('sites-enabled')
            ->getChild('000-test.local');
        $this->assertInstanceOf('org\bovigo\vfs\vfsStreamFile', $contents);
    }
}
