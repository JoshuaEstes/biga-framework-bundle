<?php

/**
 * This file is part of the Biga Framework Bundle
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace BigaFrameworkBundle\Command\Apache\Vhost;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Create an apache vhost and place it into the sites enabled directory.
 *
 * @author Joshua Estes
 */
class CreateCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('server_name', '', InputOption::VALUE_REQUIRED, 'Server host name'),
                new InputOption('port', '', InputOption::VALUE_REQUIRED, 'Port the server will be listening on', '80'),
                new InputOption('document_root', '', InputOption::VALUE_REQUIRED, 'Full path to the document root'),
                new InputOption('priority', '', InputOption::VALUE_REQUIRED, 'Priority, smaller the number the sooner it will be parsed', '000'),
                new InputOption('sites_available_dir', '', InputOption::VALUE_REQUIRED, 'Location where you will place the vhost file', '/etc/apache2/sites-enabled'),
                new InputOption('restart', '', InputOption::VALUE_NONE, 'Restarts apache after you create the vhost'),
            ))
            ->setName('apache:vhost:create')
            ->setDescription('Create an apache vhost file');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $input->setOption('document_root', realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../web'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach(array('server_name') as $option) {
            if (null === $input->getOption($option)) {
                throw new \RuntimeException(sprintf('The "%s" option must be provided.', $option));
            }
        }

        $tmpl = file_get_contents(__DIR__ . '/../../../Resources/skeleton/apache2/symfony2.vhost');
        $vhost = strtr($tmpl, array(
            '%server_name%'   => $input->getOption('server_name'),
            '%port%'          => $input->getOption('port'),
            '%document_root%' => $input->getOption('document_root'),
        ));

        file_put_contents('/tmp/symfony2.vhost', $vhost);

        $command = sprintf('sudo cp /tmp/symfony2.vhost %s/%s', $input->getOption('sites_available_dir'), $input->getOption('server_name'));
        $process = new Process($command);
        $process->run(function($type, $buffer) use($output){
            $style = 'err' === $type ? 'error' : 'info';
            $output->writeln(sprintf("<%s>%s</%s>", $style, $buffer, $style));
        });

        if ($input->getOption('restart')) {
            $this
                ->getApplication()
                ->find('apache:restart')
                ->run(new ArrayInput(array('apache:restart', '-n' => true)), $output);
        }
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog    = $this->getDialogHelper();
        $formatter = $this->getFormatterHelper();

        $output->writeln(array('',$formatter->formatBlock('Biga VHost Manager', 'bg=blue;fg=white', true),''));

        // Server Name
        $output->writeln(array(
            '',
            'The server name can be anything you want for example',
            'this can be <comment>biga.local</comment>',
            '',
        ));

        $server_name = $dialog->askandValidate($output, $this->getQuestion("ServerName", $input->getOption('server_name')), 'BigaFrameworkBundle\\Command\\Validators::validateHost', false, $input->getOption('server_name'));
        $input->setOption('server_name', $server_name);

        // Port
        $output->writeln(array(
            '',
            'Port number that you want to listen for connections on',
            '',
        ));

        $port = $dialog->askAndValidate($output, $this->getQuestion("Port", $input->getOption('port')), 'BigaFrameworkBundle\\Command\\Validators::validatePort', false, $input->getOption('port'));
        $input->setOption('port', $port);

        // Document Root
        $output->writeln(array(
            '',
            'Location of the symfony2 web dirctory',
            '',
        ));

        $document_root = $dialog->askAndValidate($output, $this->getQuestion("DocumentRoot", $input->getOption('document_root')), 'BigaFrameworkBundle\\Command\\Validators::validateDocumentRoot', false, $input->getOption('document_root'));
        $input->setOption('document_root', $document_root);

        // Priority
        $output->writeln(array(
            '',
            'The lower the priority is, the sooner apache will parse',
            'the file and it will take affect.',
            '',
        ));

        $priority = $dialog->askAndValidate($output, $this->getQuestion("Priority", $input->getOption('priority')), 'BigaFrameworkBundle\\Command\\Validators::validatePriority', false, $input->getOption('priority'));
        $input->setOption('priority', $priority);
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
