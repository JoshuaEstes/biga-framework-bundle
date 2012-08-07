<?php

/**
 * This file is part of the Biga Framework Bundle
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace BigaFrameworkBundle\Tests\Command;

use BigaFrameworkBundle\Command\Validators;

/**
 * Test to make sure the validators are working
 *
 * @author Joshua Estes
 */
class ValidatorsTest extends \PHPUnit_Framework_TestCase
{

    public function testValidateHost()
    {
        $host = 'test.local';
        $this->assertEquals($host, Validators::validateHost($host));
    }

    public function testValidateHostsFile()
    {
        $hostsFile = '/etc/hosts';
        $this->assertEquals($hostsFile, Validators::validateHostsFile($hostsFile));
    }

    public function testValidateIPAddress()
    {
        $ip = '127.0.0.1';
        $this->assertEquals($ip, Validators::validateIPAddress($ip));
    }

    public function testValidatePort()
    {
        $port = '8080';
        $this->assertEquals($port, Validators::validatePort($port));
    }

    public function testValidateDocumentRoot()
    {
        $docRoot = '/var/www/test';
        $this->assertEquals($docRoot, Validators::validateDocumentRoot($docRoot));
    }

    public function testValidatePriority()
    {
        $priority = '0000';
        $this->assertEquals($priority, Validators::validatePriority($priority));
    }

    public function testValidateDatabaseDriver()
    {
        $driver = 'pdo';
        $this->assertEquals($driver, Validators::validateDatabaseDriver($driver));
    }

    public function testValidateDatabaseHost()
    {
        $this->assertEquals('localhost', Validators::validateDatabaseHost('localhost'));
    }

    public function testValidateDatabasePort()
    {
        $this->assertEquals('3306', Validators::validateDatabasePort('3306'));
    }

    public function testValidateDatabaseName()
    {
        $this->assertEquals('biga', Validators::validateDatabaseName('biga'));
    }

    public function testValidateDatabaseUsername()
    {
        $this->assertEquals('root', Validators::validateDatabaseUsername('root'));
    }

    public function testValidateDatabasePassword()
    {
        $this->assertEquals('root', Validators::validateDatabasePassword('root'));
    }

    public function testValidateMailerTransport()
    {
        $this->assertEquals('sendmail', Validators::validateMailerTransport('sendmail'));
    }

    public function testValidateMailerHost()
    {
        $this->assertEquals('localhost', Validators::validateMailerHost('localhost'));
    }

    public function testValidateMailerUsername()
    {
        $this->assertEquals('root', Validators::validateMailerUsername('root'));
    }

    public function testValidateMailerPassword()
    {
        $this->assertEquals('root', Validators::validateMailerPassword('root'));
    }

    public function testValidateLocale()
    {
        $this->assertEquals('en', Validators::validateLocale('en'));
    }

}
