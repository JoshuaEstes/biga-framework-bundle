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
 * Configure mailer information
 *
 * @author Joshua Estes
 */
class MailerCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('transport', '', InputOption::VALUE_REQUIRED, 'Mailer transport', 'smtp'),
                new InputOption('host', '', InputOption::VALUE_REQUIRED, 'Mailer host or IP address', 'localhost'),
                new InputOption('user', '', InputOption::VALUE_REQUIRED, 'Mailer username'),
                new InputOption('password', '', InputOption::VALUE_REQUIRED, 'Mailer password'),
            ))
            ->setName('configure:mailer')
            ->setDescription('Configure the mailer')
            ->setHelp(<<<EOF
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parametersFile = $this->getContainer()->getParameter('kernel.root_dir') . '/config/parameters.yml';
        if (!is_file($parametersFile)) {
            throw new \RuntimeException(sprintf('Cannot find parameters.yml file, should be located at: %s', $parametersFile));
        }

        $parametersArray = Yaml::parse($parametersFile);
        $parametersArray['parameters'] = array_merge($parametersArray['parameters'],array(
            'mailer_transport' => $input->getOption('transport'),
            'mailer_host'      => $input->getOption('host'),
            'mailer_user'      => $input->getOption('user'),
            'mailer_password'  => $input->getOption('password'),
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

        $dialog->writeSection($output, 'Biga Mailer Configurator');

        // Mailer Transport
        $output->writeln(array(
            '',
            'The mailer transport can be any one of the following:',
            '',
            '<comment>smtp</comment>',
            '<comment>gmail</comment>',
            '<comment>mail</comment>',
            '<comment>sendmail</comment>',
            '',
            '<info>NOTE: Some of these transports require a username, password, host, and port.</info>',
            '',
        ));

        $transport = $dialog->askAndValidate($output, $dialog->getQuestion("Mailer Transport", $input->getOption('transport')), function($value) {
            if (!in_array($value, array('smtp', 'gmail', 'mail', 'sendmail'))) {
                throw new \InvalidArgumentException('Invalid transport');
            }
            return $value;
        }, false, $input->getOption('transport'));
        $input->setOption('transport', $transport);

        // Mailer Host
        $output->writeln(array(
            '',
            'Hostname or IP Address of the host mailer',
            '',
        ));

        $host = $dialog->askAndValidate($output, $dialog->getQuestion("Mailer Host", $input->getOption('host')), function($value) use($validator) {
            $errors = $validator->validateValue($value, new Assert\NotBlank());
            if (count($errors)) {
                throw new \InvalidArgumentException(trim(str_replace(":\n", ":", (string) $errors)));
            }
            return $value;
        }, false, $input->getOption('host'));
        $input->setOption('host', $host);

        // Mailer Username
        $output->writeln(array(
            '',
            'Depending upon what transport type you selected this will be required.',
            '',
        ));

        $user = $dialog->askAndValidate($output, $dialog->getQuestion("Mailer Username", $input->getOption('user')), function($value) use($validator, $input) {
            if (in_array($input->getOption('transport'), array('gmail'))) {
                $errors = $validator->validateValue($value, new Assert\NotBlank());
                if (count($errors)) {
                    throw new \InvalidArgumentException(trim(str_replace(":\n", ":", (string) $errors)));
                }
            }
            return $value;
        }, false, $input->getOption('user'));
        $input->setOption('user', $user);

        // Mailer Password
        $output->writeln(array(
            '',
            'Depending on what transport you selected, this may be required.',
            '',
        ));

        $password = $dialog->askAndValidate($output, $dialog->getQuestion("Mailer Password", $input->getOption('password')), function($value) use($validator, $input) {
            if (in_array($input->getOption('transport'), array('gmail'))) {
                $errors = $validator->validateValue($value, new Assert\NotBlank());
                if (count($errors)) {
                    throw new \InvalidArgumentException(trim(str_replace(":\n", ":", (string) $errors)));
                }
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
