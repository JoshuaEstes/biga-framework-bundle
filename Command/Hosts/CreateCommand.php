<?php

namespace BigaFrameworkBundle\Command\Hosts;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class CreateCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('hosts_file', '', InputOption::VALUE_REQUIRED, 'Path to your hosts file, this is different for every OS', '/etc/hosts'),
                new InputOption('ip_address', '', InputOption::VALUE_REQUIRED, 'IP Address of the server', '127.0.0.1'),
                new InputOption('host', '', InputOption::VALUE_REQUIRED, 'The host name you want. If you want more than one, please use a space between them'),
            ))
            ->setName('hosts:create')
            ->setDescription('Add an entry in your hosts file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach(array('host') as $option) {
            if (null === $input->getOption($option)) {
                throw new \RuntimeException(sprintf('The "%s" option must be provided.', $option));
            }
        }

        $command = sprintf("echo \"%s %s\" | sudo tee -a %s", $input->getOption('ip_address'), $input->getOption('host'), $input->getOption('hosts_file'));
        $process = new Process($command);
        $process->run(function($type, $buffer) use($output){
            $style = 'err' === $type ? 'error' : 'info';
            $output->writeln(sprintf("<%s>%s</%s>", $style, $buffer, $style));
        });
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog    = $this->getDialogHelper();
        $formatter = $this->getFormatterHelper();

        $output->writeln(array('',$formatter->formatBlock('Biga Host File Management', 'bg=blue;fg=white', true),''));

        // Location of hosts file
        $output->writeln(array(
            '',
            'Please enter the location of your hosts file. The default location',
            'should work with Mac and Linux. If you are on windows you will need',
            'to change this here.',
            '',
        ));

        $hosts_file = $dialog->askAndValidate($output, $this->getQuestion("Location of hosts file", $input->getOption('hosts_file')), 'BigaFrameworkBundle\\Command\\Validators::validateHostsFile', false, $input->getOption('hosts_file'));
        $input->setOption('hosts_file', $hosts_file);

        // IP Address
        $output->writeln(array(
            '',
            'Enter the IP Address, this can be any valid IP address.',
            '',
        ));
        $ip_address = $dialog->askAndValidate($output, $this->getQuestion("IP Address", $input->getOption('ip_address')), 'BigaFrameworkBundle\\Command\\Validators::validateIPAddress', false, $input->getOption('ip_address'));
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
        $host = $dialog->askAndValidate($output, $this->getQuestion("Host(s)", $input->getOption('host')), 'BigaFrameworkBundle\\Command\\Validators::validateHost', false, $input->getOption('host'));
        $input->setOption('host', $host);
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
