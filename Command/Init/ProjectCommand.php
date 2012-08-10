<?php

/**
 * This file is part of the Biga Framework Bundle
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace BigaFrameworkBundle\Command\Init;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Initialize a symfony 2 project for the first time. This will take care
 * of most tasks that are done by hand.
 *
 * @author Joshua Estes
 */
class ProjectCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog     = $this->getDialogHelper();
        $parameters = Yaml::parse(file_get_contents($this->getConfigFile('parameters.yml')));

        $dialog->writeSection($output, 'Biga Initialize Project');

        // Secret
        $output->writeln(array(
            '',
            'The secret that will be generated is used as the crsf token. It is recommended that you',
            'generate a token here.',
            '',
        ));

        if ($dialog->askConfirmation($output, $dialog->getQuestion("Do you want to generate a secret", 'yes', '?'), true)) {
            $parameters['parameters'] = array_merge($parameters['parameters'], array(
                'secret' => md5(uniqid(time(),true)),
            ));
            file_put_contents($this->getConfigFile('parameters.yml'), Yaml::dump($parameters));
        }

        // Locale
        $output->writeln(array(
            '',
            'The default locale that the framework will use.',
            '',
        ));

        $locale = $dialog->askAndValidate($output, $dialog->getQuestion("Default Locale", 'en'), 'BigaFrameworkBundle\\Command\\Validators::validateLocale', false, 'en');
        $parameters['parameters'] = array_merge($parameters['parameters'], array(
            'locale' => $locale,
        ));
        file_put_contents($this->getConfigFile('parameters.yml'), Yaml::dump($parameters));

        // Database
        $output->writeln(array(
            '',
            'You are about to setup the database. If you choose to configure the database then you will be',
            'able to create the database. However if you skip this part, then you will need to configure',
            'and create the database manually.',
            '',
        ));
        if ($dialog->askConfirmation($output, $dialog->getQuestion('Do you want to setup the database', 'yes', '?'), true)) {
            $this
                ->getApplication()
                ->find('configure:database')
                ->run($input, $output);
            if ($dialog->askConfirmation($output, $dialog->getQuestion('Do you want to create the database', 'yes', '?'), true)) {
                $this
                    ->getApplication()
                    ->find('doctrine:database:create')
                    ->run($input, $output);
            }
        }

        // Mailer
        $output->writeln(array(
            '',
            'This will configure the mailer that the framework will use.',
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
            'If you are using apache then you can setup a vhost and restart apache here. If you are',
            'using another web server such as nginx, then please skip this part.',
            '',
        ));

        if ($dialog->askConfirmation($output, $dialog->getQuestion('Do you want to create an apache vhost', 'yes', '?'), true)) {
            $this
                ->getApplication()
                ->find('apache:vhost:create')
                ->run(new ArrayInput(array(
                    'command'    => 'apache:vhost:create',
                    '--use_sudo' => true,
                )), $output);
            if ($dialog->askConfirmation($output, $dialog->getQuestion('Do you want to restart apache', 'yes', '?'), true)) {
                $this
                    ->getApplication()
                    ->find('apache:restart')
                    ->run(new ArrayInput(array(
                        'command' => 'apache:restart',
                        '-n'      => true,
                    )), $output);
            }
        }

        // hosts file
        $output->writeln(array(
            '',
            'Updating your hosts file will allow you to type the url into your browser and it',
            'will use your hosts file to resolve that hostname.',
            '',
        ));

        if ($dialog->askConfirmation($output, $dialog->getQuestion('Do you want to update your hosts file', 'yes', '?'), true)) {
            $this
                ->getApplication()
                ->find('hosts:create')
                ->run(new ArrayInput(array(
                    'command'    => 'hosts:create',
                    '--use_sudo' => true,
                )), $output);
        }

    }

    protected function getDialogHelper()
    {
        $dialog = $this->getHelperSet()->get('dialog');
        if (!$dialog || get_class($dialog) !== 'BigaFrameworkBundle\Helper\DialogHelper') {
            $this->getHelperSet()->set($dialog = new \BigaFrameworkBundle\Helper\DialogHelper());
        }
        return $dialog;
    }

    protected function getConfigFile($file)
    {
        return $this->getContainer()->getParameter('kernel.root_dir') . '/config/' . $file;
    }

}
