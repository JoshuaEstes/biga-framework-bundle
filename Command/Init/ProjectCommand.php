<?php

namespace BigaFrameworkBundle\Command\Init;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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

        if (!is_file($this->getConfigFile('parameters.yml')) && is_file($this->getConfigFile('parameters.yml.dist'))) {
            $filesystem = new Filesystem();
            $filesystem->copy($parametersDistFile, $parametersFile);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog    = $this->getDialogHelper();
        $formatter = $this->getFormatterHelper();

        $output->writeln(array('',$formatter->formatBlock('Biga Initialize Project', 'bg=blue;fg=white', true),''));

        // Secret
        $output->writeln(array(
            '',
            '',
        ));

        if ($dialog->askConfirmation($output, $this->getQuestion("Do you want to generate a secret", 'Y/n'), true)) {
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

        if ($dialog->askConfirmation($output, $this->getQuestion("Locale", 'en'), 'en')) {
        }
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

    protected function getConfigFile($file)
    {
        return $this->getContainer()->getParameter('kernel.root_dir') . '/config/' . $file;
    }

}
