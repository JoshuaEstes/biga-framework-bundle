<?php

/**
 * This file is part of the Biga Framework Bundle
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace BigaFrameworkBundle\Command\Apache;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Restarts apache web server
 *
 * @author Joshua Estes
 */
class RestartCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('apache:restart')
            ->setDescription('Restart Apache web server')
            ->setHelp(<<<EOF

This command will restart apache2 web server.

EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();

        if ($input->isInteractive()) {
            if (!$dialog->askConfirmation($output, $dialog->getQuestion('Are you sure you want to restart apache', 'yes', '?'), true)) {
                $output->writeln('<error>Command Aborted</error>');
                return 1;
            }
        }

        // Let's play find that apache2!
        $process = new Process(<<<EOF
bash -i -c "
if [ -e /etc/init.d/apache2 ]; then
    sudo /etc/init.d/apache2 restart
elif [ "$(which apachectl)" ]; then
    sudo $(which apachectl) -k restart
fi"
EOF
);
        $process->run(function($type, $buffer) use($output) {
            $style = ('err' === $type) ? 'error' : 'info';
            $output->writeln(sprintf('<%s>%s</%s>', $style, $buffer, $style));
        });
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
