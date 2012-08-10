<?php

/**
 * This file is part of the Biga Framework Bundle
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace BigaFrameworkBundle\Command\Apache\Vhost;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Create an apache vhost and place it into the sites enabled directory.
 *
 * @author Joshua Estes
 */
class CreateCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
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
                new InputOption('use_sudo', '', InputOption::VALUE_NONE, 'use sudo to write the file'),
            ))
            ->setName('apache:vhost:create')
            ->setDescription('Create an apache vhost file')
            ->setHelp(<<<EOF

This will create a vhost file with a default configuration for symfony2
projects.

EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if (null === $input->getOption('document_root')) {
            $input->setOption('document_root', realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../web'));
        }
    }

    /**
     * {@inheritdoc}
     */
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

        $outputFile = sprintf('%s/%s-%s', $input->getOption('sites_available_dir'), $input->getOption('priority'), $input->getOption('server_name'));
        if ($input->getOption('use_sudo')) {
            $tempFile = sprintf('%s/symfony2.vhost', sys_get_temp_dir());
            file_put_contents($tempFile, $vhost);
            $command = sprintf('sudo cp %s %s', $tempFile, $outputFile);
            $process = new Process($command);
            $process->run(function($type, $buffer) use($output){
                $style = 'err' === $type ? 'error' : 'info';
                $output->writeln(sprintf("<%s>%s</%s>", $style, $buffer, $style));
            });
            unlink($tempFile);
        } else {
            file_put_contents($outputFile, $vhost);
        }

        if ($input->getOption('restart')) {
            $this
                ->getApplication()
                ->find('apache:restart')
                ->run(new ArrayInput(array('apache:restart', '-n' => true)), $output);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog    = $this->getDialogHelper();
        $validator = Validation::createValidator();

        $dialog->writeSection($output, 'Biga Apache vhost Creator');

        // Server Name
        $output->writeln(array(
            '',
            'The server name can be anything you want for example',
            'this can be <comment>biga.local</comment>',
            '',
        ));

        $server_name = $dialog->askandValidate($output, $dialog->getQuestion("ServerName", $input->getOption('server_name')), function($value) use($validator) {
            $errors = $validator->validateValue($value, new Assert\NotBlank());
            if (count($errors)) {
                throw new \InvalidArgumentException(trim(str_replace(":\n", ":", (string) $errors)));
            }
            return $value;
        }, false, $input->getOption('server_name'));
        $input->setOption('server_name', $server_name);

        // Port
        $output->writeln(array(
            '',
            'Port number that you want to listen for connections on',
            '',
        ));

        $port = $dialog->askAndValidate($output, $dialog->getQuestion("Port", $input->getOption('port')), function($value) use($validator) {
            $errors = $validator->validateValue($value, new Assert\Type('numeric'));
            if (count($errors)) {
                throw new \InvalidArgumentException(trim(str_replace(":\n", ":", (string) $errors)));
            }
            return $value;
        }, false, $input->getOption('port'));
        $input->setOption('port', $port);

        // Document Root
        $output->writeln(array(
            '',
            'Location of the symfony2 web dirctory',
            '',
        ));

        $document_root = $dialog->askAndValidate($output, $dialog->getQuestion("DocumentRoot", $input->getOption('document_root')), function($value) {
            if (!is_dir($value)) {
                throw new \InvalidArgumentException('Cannot find directory.');
            }
            return $value;
        }, false, $input->getOption('document_root'));
        $input->setOption('document_root', $document_root);

        // Priority
        $output->writeln(array(
            '',
            'The lower the priority is, the sooner apache will parse',
            'the file and it will take affect.',
            '',
        ));

        $priority = $dialog->askAndValidate($output, $dialog->getQuestion("Priority", $input->getOption('priority')), function($value) use($validator) {
            $errors = $validator->validateValue($value, new Assert\Type('numeric'));
            if (count($errors)) {
                throw new \InvalidArgumentException(trim(str_replace(":\n", ":", (string) $errors)));
            }
            return $value;
        }, false, $input->getOption('priority'));
        $input->setOption('priority', $priority);
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
