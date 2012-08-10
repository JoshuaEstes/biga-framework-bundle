<?php

/**
 * This file is part of the Biga Framework Bundle
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace BigaFrameworkBundle\Command\Hosts;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Add an entry in the users hosts file
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
                new InputOption('hosts_file', '', InputOption::VALUE_REQUIRED, 'Path to your hosts file, this is different for every OS', '/etc/hosts'),
                new InputOption('ip_address', '', InputOption::VALUE_REQUIRED, 'IP Address of the server', '127.0.0.1'),
                new InputOption('host', '', InputOption::VALUE_REQUIRED, 'The host name you want. If you want more than one, please use a space between them'),
                new InputOption('use_sudo', '', InputOption::VALUE_NONE, 'Append the file using sudo.'),
            ))
            ->setName('hosts:create')
            ->setDescription('Add an entry in your hosts file')
            ->setHelp(<<<EOF

This command will add an entry in your hosts file. It's setup
to use the host file in the location that is default on Mac
and linux machines. If you are on a windows machine, you will
need to give it a different hosts file location.

If you need to have admistrator access, you must use the --use_sudo
option.

    <info>php app/console hosts:create --use_sudo</info>

EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach(array('host') as $option) {
            if (null === $input->getOption($option)) {
                throw new \RuntimeException(sprintf('The "%s" option must be provided.', $option));
            }
        }

        if ($input->getOption('use_sudo')) {
            $command = sprintf("echo \"%s %s\" | sudo tee -a %s", $input->getOption('ip_address'), $input->getOption('host'), $input->getOption('hosts_file'));
            $process = new Process($command);
            $process->run(function($type, $buffer) use($output){
                $style = 'err' === $type ? 'error' : 'info';
                $output->writeln(sprintf("<%s>%s</%s>", $style, $buffer, $style));
            });
        } else {
            if (!is_writeable($input->getOption('hosts_file'))) {
                throw new \RuntimeException('Cannot write to file.');
            }
            $handle = fopen($input->getOption('hosts_file'), 'a', false);
            fwrite($handle, sprintf("%s %s", $input->getOption('ip_address'), $input->getOption('host')));
            fclose($handle);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog    = $this->getDialogHelper();
        $validator = Validation::createValidator();

        $dialog->writeSection($output, 'Biga Host File Management');

        // Location of hosts file
        $output->writeln(array(
            '',
            'Please enter the location of your hosts file. The default location',
            'should work with Mac and Linux. If you are on windows you will need',
            'to change this here.',
            '',
        ));

        $hosts_file = $dialog->askAndValidate($output, $dialog->getQuestion("Location of hosts file", $input->getOption('hosts_file')), function($value) {
            if (!is_file($value)) {
                throw new \InvalidArgumentException('Cannot find hosts file');
            }
            return $value;
        }, false, $input->getOption('hosts_file'));
        $input->setOption('hosts_file', $hosts_file);

        // IP Address
        $output->writeln(array(
            '',
            'Enter the IP Address, this can be any valid IP address.',
            '',
        ));
        $ip_address = $dialog->askAndValidate($output, $dialog->getQuestion("IP Address", $input->getOption('ip_address')), function($value) use($validator) {
            $errors = $validator->validateValue($value, new Assert\Ip(array('version' => 'all')));
            if (count($errors)) {
                throw new \InvalidArgumentException(trim(str_replace(":\n", ":", (string) $errors)));
            }
            return $value;
        }, false, $input->getOption('ip_address'));
        $input->setOption('ip_address', $ip_address);

        // host
        $output->writeln(array(
            '',
            'Enter as many hosts as you want for this IP address. If you enter more than one',
            'you must seperate them by spaces. As an example:',
            '',
            '<comment>biga.local biga</comment>',
            '',
        ));
        $host = $dialog->askAndValidate($output, $dialog->getQuestion("Host(s)", $input->getOption('host')), function($value) use($validator) {
            $errors = $validator->validateValue($value, new Assert\NotBlank());
            if (count($errors)) {
                throw new \InvalidArgumentException(trim(str_replace(":\n", ":", (string) $errors)));
            }
            return $value;
        }, false, $input->getOption('host'));
        $input->setOption('host', $host);
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
