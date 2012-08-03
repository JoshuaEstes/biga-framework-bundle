<?php

namespace BigaFrameworkBundle\Command\Configure;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class DatabaseCommand extends ContainerAwareCommand
{

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

        $output->writeln('Parameters file has been updated.');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog    = $this->getDialogHelper();
        $formatter = $this->getFormatterHelper();

        $output->writeln(array('',$formatter->formatBlock('Biga Database Configurator', 'bg=blue;fg=white', true),''));

        /**
         * This is used to setup the Doctrine database, in the future this will need
         * to be updated to configure more than just doctrine (ie Propel)
         */

        // Database Driver
        $output->writeln(array(
            '',
            'The database driver',
            '',
        ));

        $driver = $dialog->askAndValidate($output, $this->getQuestion("Database Driver", $input->getOption('driver')), 'BigaFrameworkBundle\\Command\\Validators::validateDatabaseDriver', false, $input->getOption('driver'));
        $input->setOption('driver', $driver);

        // Database host
        $output->writeln(array(
            '',
            'The database host, usually localhost or 127.0.0.1',
            '',
        ));

        $host = $dialog->askAndValidate($output, $this->getQuestion("Database Host", $input->getOption('host')), 'BigaFrameworkBundle\\Command\\Validators::validateDatabaseHost', false, $input->getOption('host'));
        $input->setOption('host', $host);

        // Database Port
        $output->writeln(array(
            '',
            'Database port, MySQL default is 3306',
            '',
        ));

        $port = $dialog->askAndValidate($output, $this->getQuestion("Database Port", $input->getOption('port')), 'BigaFrameworkBundle\\Command\\Validators::validateDatabasePort', false, $input->getOption('port'));
        $input->setOption('port', $port);

        // Database Name
        $output->writeln(array(
            '',
            'Database name',
            '',
        ));

        $name = $dialog->askAndValidate($output, $this->getQuestion("Database Name", $input->getOption('name')), 'BigaFrameworkBundle\\Command\\Validators::validateDatabaseName', false, $input->getOption('name'));
        $input->setOption('name', $name);

        // Database User
        $output->writeln(array(
            '',
            '',
            '',
        ));

        $user = $dialog->askAndValidate($output, $this->getQuestion("Database Username", $input->getOption('user')), 'BigaFrameworkBundle\\Command\\Validators::validateDatabaseUsername', false, $input->getOption('user'));
        $input->setOption('user', $user);

        // Database Password
        $output->writeln(array(
            '',
            '',
            '',
        ));

        $password = $dialog->askAndValidate($output, $this->getQuestion("Database Password", $input->getOption('password')), 'BigaFrameworkBundle\\Command\\Validators::validateDatabasePassword', false, $input->getOption('password'));
        $input->setOption('password', $password);
    }

    protected function getDialogHelper()
    {
        return $this->getHelperSet()->get('dialog');
    }

    protected function getFormatterHelper()
    {
        return $this->getHelperSet()->get('formatter');
    }

    protected function getQuestion($question, $default)
    {
        return sprintf('<info>%s</info> [<comment>%s</comment>]: ', $question, $default);
    }

}
