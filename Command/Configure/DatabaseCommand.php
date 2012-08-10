<?php

/**
 * This file is part of the Biga Framework Bundle
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace BigaFrameworkBundle\Command\Configure;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;

/**
 * Configure database settings
 *
 * @author Joshua Estes
 */
class DatabaseCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('driver', '', InputOption::VALUE_REQUIRED, 'Database driver', 'pdo_mysql'),
                new InputOption('host', '', InputOption::VALUE_REQUIRED, 'Database host or IP address', 'localhost'),
                new InputOption('port', '', InputOption::VALUE_REQUIRED, 'Database port', '3306'),
                new InputOption('name', '', InputOption::VALUE_REQUIRED, 'Database name', 'biga_dev'),
                new InputOption('user', '', InputOption::VALUE_REQUIRED, 'Database username', 'root'),
                new InputOption('password', '', InputOption::VALUE_REQUIRED, 'Database password'),
            ))
            ->setName('configure:database')
            ->setDescription('')
            ->setHelp(<<<EOF
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach(array('password') as $option) {
            if (null === $input->getOption($option)) {
                throw new \RuntimeException(sprintf('The "%s" option must be provided.', $option));
            }
        }

        $parametersFile = $this->getContainer()->getParameter('kernel.root_dir') . '/config/parameters.yml';
        if (!is_file($parametersFile)) {
            throw new \RuntimeException(sprintf('Cannot find parameters.yml file, should be located at: %s', $parametersFile));
        }

        $parametersArray = Yaml::parse($parametersFile);
        $parametersArray['parameters'] = array_merge($parametersArray['parameters'],array(
            'database_driver'   => $input->getOption('driver'),
            'database_host'     => $input->getOption('host'),
            'database_port'     => $input->getOption('port'),
            'database_name'     => $input->getOption('name'),
            'database_user'     => $input->getOption('user'),
            'database_password' => $input->getOption('password'),
        ));

        file_put_contents($parametersFile, Yaml::dump($parametersArray));

        $output->writeln(array(
            '',
            'Parameters file has been updated.',
            '',
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog    = $this->getDialogHelper();
        $validator = Validation::createValidator();

        $dialog->writeSection($output, 'Biga Database Configurator');

        /**
         * This is used to setup the Doctrine database, in the future this will need
         * to be updated to configure more than just doctrine (ie Propel)
         */

        // Database Driver
        $output->writeln(array(
            '',
            'The database driver, the currnetly support drivers are:',
            '',
            '<comment>pdo_mysql</comment> - MySQL',
            '<comment>pdo_sqlite</comment> - SQLite',
            '<comment>pdo_pgsql</comment> - PostgreSQL',
            '<comment>pdo_oci</comment> - Oracle (may cause issues)',
            '<comment>pdo_sqlsrv</comment> - MSSQL',
            '<comment>oci8</comment> - Oracle',
            '',
        ));

        $driver = $dialog->askAndValidate($output, $dialog->getQuestion("Database Driver", $input->getOption('driver')), function($value) {
            if (!in_array(array('pdo_mysql', 'pdo_sqlite', 'pdo_pgsql', 'pdo_oci', 'pdo_sqlsrv', 'oci8'), $value)) {
                throw new \InvalidArgumentException('Invalid database driver');
            }
            return $value;
        }, false, $input->getOption('driver'));
        $input->setOption('driver', $driver);

        // Database host
        $output->writeln(array(
            '',
            'The database host, usually localhost or 127.0.0.1',
            '',
        ));

        $host = $dialog->askAndValidate($output, $dialog->getQuestion("Database Host", $input->getOption('host')), function ($value) use($validator) {
            $errors = $validator->validateValue($value, new Assert\NotBlank());
            if (count($errors)) {
                throw new \InvalidArgumentException(trim(str_replace(":\n", ":", (string) $errors)));
            }
            return $value;
        }, false, $input->getOption('host'));
        $input->setOption('host', $host);

        // Database Port
        $output->writeln(array(
            '',
            'Database port, MySQL default is 3306',
            '',
        ));

        $port = $dialog->askAndValidate($output, $dialog->getQuestion("Database Port", $input->getOption('port')), function($value) use($validator) {
            $errors = $validator->validateValue($value, new Assert\Type('numeric'));
            if (count($errors)) {
                throw new \InvalidArgumentException(trim(str_replace(":\n", ":", (string) $errors)));
            }
            return $value;
        }, false, $input->getOption('port'));
        $input->setOption('port', $port);

        // Database Name
        $output->writeln(array(
            '',
            'Database name',
            '',
        ));

        $name = $dialog->askAndValidate($output, $dialog->getQuestion("Database Name", $input->getOption('name')), function($value) use($validator) {
            $errors = $validator->validateValue($value, new Assert\NotBlank());
            if (count($errors)) {
                throw new \InvalidArgumentException(trim(str_replace(":\n", ":", (string) $errors)));
            }
            return $value;
        }, false, $input->getOption('name'));
        $input->setOption('name', $name);

        // Database User
        $output->writeln(array(
            '',
            'Username that you want to use to log into the database',
            '',
        ));

        $user = $dialog->askAndValidate($output, $dialog->getQuestion("Database Username", $input->getOption('user')), function($value) use($validator) {
            $errors = $validator->validateValue($value, new Assert\NotBlank());
            if (count($errors)) {
                throw new \InvalidArgumentException(trim(str_replace(":\n", ":", (string) $errors)));
            }
            return $value;
        }, false, $input->getOption('user'));
        $input->setOption('user', $user);

        // Database Password
        $output->writeln(array(
            '',
            'Password for the user. If this is blank it may cause database issues',
            'so this is required.',
            '',
        ));

        $password = $dialog->askAndValidate($output, $dialog->getQuestion("Database Password", $input->getOption('password')), function($value) use($validator) {
            $errors = $validator->validateValue($value, new Assert\NotBlank());
            if (count($errors)) {
                throw new \InvalidArgumentException(trim(str_replace(":\n", ":", (string) $errors)));
            }
            return $value;
        }, false, $input->getOption('password'));
        $input->setOption('password', $password);
    }

    protected function getDialogHelper()
    {
        $dialog = $this->getHelperSet()->get('dialog');
        if (!$dialog || get_class($dialog) !== 'BigaFrameworkBundle\Helper\DialogHelper') {
            $this->getHelperSet()->set($dialog = new \BigaFrameworkBundle\Helper\DialogHelper());
        }
        return $dialog;
    }

}
