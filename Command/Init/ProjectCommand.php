<?php

namespace BigaFrameworkBundle\Command\Init;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

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
and creating a vhost entry for you. This will also create a crsf
token for you.
EOF
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $ouput)
    {
        if (!$input->isInteractive()) {
            throw new \RuntimeException('This command must be run in interactive mode.');
        }

        // This will ignore changes to your parameters.yml file
        $process = new Process('git update-index --assume-unchanged app/config/parameters.yml');
        $process->run();

        // Make sure the directories are set to correct permissions
        $process = new Process(sprintf('chmod 0777 app/{cache,log}'));
        $process->run();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog    = $this->getDialogHelper();
        $formatter = $this->getFormatterHelper();

        $dialog->writeSection($output, 'Biga Initialize Project');

        // Secret
        $output->writeln(array(
            '',
            '',
        ));

        if ($dialog->askConfirmation($output, $dialog->getQuestion("Do you want to generate a secret", 'yes', '?'), true)) {
            $parameters = Yaml::parse(file_get_contents($this->getConfigFile('parameters.yml')));
            $parameters['parameters'] = array_merge($parameters['parameters'], array(
                'secret' => md5(uniqid(time(),true)),
            ));
            file_put_contents($this->getConfigFile('parameters.yml'), Yaml::dump($parameters));
        }

        // Locale
        $output->writeln(array(
            '',
            '',
        ));

        $locale = $dialog->askAndValidate($output, $dialog->getQuestion("Locale", 'en'), 'BigaFrameworkBundle\\Command\\Validators::validateLocale', false, 'en');

        // Database
        $output->writeln(array(
            '',
            '',
        ));
        if ($dialog->askConfirmation($output, $dialog->getQuestion('Do you want to setup the database', 'yes', '?'), true)) {
            $this
                ->getApplication()
                ->find('configure:database')
                ->run($input, $output);
        }

        // Mailer
        $output->writeln(array(
            '',
            '',
        ));
        if ($dialog->askConfirmation($output, $dialog->getQuestion('Do you want to setup the mailer', 'yes', '?'), true)) {
            $this
                ->getApplication()
                ->find('configure:mailer')
                ->run($input, $output);
        }

        // Web server
        $output->writeln(array(
            '',
            '',
            '',
        ));

        if ($dialog->askConfirmation($output, $dialog->getQuestion('Do you want to create an apache vhost', 'yes', '?'), true)) {
            $this
                ->getApplication()
                ->find('apache:vhost:create')
                ->run($input, $output);
            if ($dialog->askConfirmation($output, $dialog->getQuestion('Do you want to restart apache', 'yes', '?'), true)) {
                $this
                    ->getApplication()
                    ->find('apache:restart')
                    ->run(array_merge($input, array('-n' => true)), $output);
            }
        }
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }

    protected function getDialogHelper()
    {
        $dialog = $this->getHelperSet()->get('dialog');
        if (!$dialog || get_class($dialog) !== 'BigaFrameworkBundle\Helper\DialogHelper') {
            $this->getHelperSet()->set($dialog = new \BigaFrameworkBundle\Helper\DialogHelper());
        }
        return $dialog;
    }

    protected function getFormatterHelper()
    {
        return $this->getHelperSet()->get('formatter');
    }

    protected function getQuestion($question, $default)
    {
        return sprintf('<info>%s</info> [<comment>%s</comment>]: ', $question, $default);
    }

    protected function getConfigFile($file)
    {
        return $this->getContainer()->getParameter('kernel.root_dir') . '/config/' . $file;
    }

}
