<?php

namespace BigaFrameworkBundle\Command\Configure;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MailerCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('transport', '', InputOption::VALUE_REQUIRED, 'Mailer transport', 'mail'),
                new InputOption('host', '', InputOption::VALUE_REQUIRED, 'Mailer host or IP address'),
                new InputOption('user', '', InputOption::VALUE_REQUIRED, 'Mailer username'),
                new InputOption('password', '', InputOption::VALUE_REQUIRED, 'Mailer password'),
            ))
            ->setName('configure:mailer')
            ->setDescription('Configure the mailer')
            ->setHelp(<<<EOF
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parametersFile = $this->getContainer()->getParameter('kernel.root_dir') . '/config/parameters.yml';
        if (!is_file($parametersFile)) {
            throw new \RuntimeException(sprintf('Cannot find parameters.yml file, should be located at: %s', $parametersFile));
        }

        $parametersArray = Yaml::parse($parametersFile);
        $updatedParametersArray = array_merge($parametersArray['parameters'],array(
            'mailer_transport' => $input->getOption('transport'),
            'mailer_host'      => $input->getOption('host'),
            'mailer_user'      => $input->getOption('user'),
            'mailer_password'  => $input->getOption('password'),
        ));

        file_put_contents($parametersFile, Yaml::dump($updatedParametersArray));

        $output->writeln('Parameters file has been updated.');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog    = $this->getDialogHelper();
        $formatter = $this->getFormatterHelper();

        $output->writeln(array('',$formatter->formatBlock('Biga Mailer Configurator', 'bg=blue;fg=white', true),''));

        // Mailer Transport
        $output->writeln(array(
            '',
            'The mailer transport can be any one of the following:',
            '',
            '<comment>',
            'smtp',
            'gmail',
            'mail',
            'sendmail',
            '</comment>',
            '',
            '<info>Some of these transports require a username, password, host, and port.</info>',
            '',
        ));

        $transport = $dialog->askAndValidate($output, $this->getQuestion("Mailer Transport", $input->getOption('transport')), 'BigaFrameworkBundle\\Command\\Validators::validateMailerTransport', false, $input->getOption('transport'));
        $input->setOption('transport', $transport);

        // Mailer Host
        $output->writeln(array(
            '',
            '',
            '',
        ));

        $host = $dialog->askAndValidate($output, $this->getQuestion("Mailer Host", $input->getOption('host')), 'BigaFrameworkBundle\\Command\\Validators::validateMailerHost', false, $input->getOption('host'));
        $input->setOption('host', $host);

        // Mailer Username
        $output->writeln(array(
            '',
            '',
            '',
        ));

        $user = $dialog->askAndValidate($output, $this->getQuestion("Mailer Username", $input->getOption('user')), 'BigaFrameworkBundle\\Command\\Validators::validateMailerUsername', false, $input->getOption('user'));
        $input->setOption('user', $user);

        // Mailer Password
        $output->writeln(array(
            '',
            '',
            '',
        ));

        $password = $dialog->askAndValidate($output, $this->getQuestion("Mailer Password", $input->getOption('password')), 'BigaFrameworkBundle\\Command\\Validators::validateMailerPassword', false, $input->getOption('password'));
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
