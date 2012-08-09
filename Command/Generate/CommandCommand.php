<?php

/**
 * This file is part of the Biga Framework Bundle
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace BigaFrameworkBundle\Command\Generate;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Generate a command
 *
 * @author Joshua Estes
 */
class CommandCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('name', '', InputOption::VALUE_REQUIRED, 'Command Name'),
                new InputOption('description', '', InputOption::VALUE_REQUIRED, 'Description of command'),
                new InputOption('bundle', '', InputOption::VALUE_REQUIRED, 'Name of bundle to place this command in'),
            ))
            ->setName('generate:command')
            ->setDescription('Generate a command');
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog    = $this->getDialogHelper();
        $validator = Validation::createValidator();
        $container = $this->getContainer();

        $dialog->writeSection($output, 'Biga Command Generator');

        // name
        $output->writeln(array(
            '',
            'The command name should be at least <info>command:name</info>',
            '',
        ));

        $name = $dialog->askAndValidate($output, $dialog->getQuestion('Command Name', $input->getOption('name')), function($value) {
            if (!strpos($value, ':')) {
                throw new \InvalidArgumentException('Please name the command "command:name"');
            }
            return $value;
        }, false, $input->getOption('name'));
        $input->setOption('name', $name);

        // description
        $output->writeln(array(
            '',
            'Enter a short description of what this command will do.',
            '',
        ));

        $description = $dialog->askAndValidate($output, $dialog->getQuestion('Command description', $input->getOption('description')), function($value) use($validator) {
            $errors = $validator->validateValue($value, new Assert\NotBlank());
            if (count($errors)) {
                throw new \InvalidArgumentException(trim(str_replace(":\n", ":", (string) $errors)));
            }
            return $value;
        }, false, $input->getOption('description'));
        $input->setOption('description', $description);

        // Bundle
        $output->writeln(array(
            '',
            'Please enter the name of the bundle you want to add the command to. This bundle',
            'should be in the src directory.',
            '',
            'Examples include: AcmeDemoBundle',
            '',
        ));

        $bundle = $dialog->askAndValidate($output, $dialog->getQuestion('Bundle', $input->getOption('bundle')), function($value) use($validator, $container) {
            $errors = $validator->validateValue($value, new Assert\NotBlank());
            if (count($errors)) {
                throw new \InvalidArgumentException(trim(str_replace(":\n", ":", (string) $errors)));
            }
            $bundles = $container->getParameter('kernel.bundles');
            if (!in_array($value, array_keys($bundles))) {
                throw new\InvalidArgumentException('Invalid Bundle name');
            }
            return $value;
        }, false, $input->getOption('bundle'));
        $input->setOption('bundle', $bundle);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach(array('name', 'bundle') as $option) {
            if (null === $input->getOption($option)) {
                throw new \RuntimeException(sprintf('The "%s" option must be provided.', $option));
            }
        }

        $bundles    = $this->getContainer()->getParameter('kernel.bundles');
        $formatter  = $this->getHelperSet()->get('formatter');
        $filesystem = new Filesystem();

        try {
            $reflect = new \ReflectionClass($bundles[$input->getOption('bundle')]);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        if (strpos($reflect->getFileName(), 'vendor')) {
            $output->writeln($formatter->formatBlock(array(
                '',
                'This is going into a bundle in the vendor directory. Please be',
                'aware that this is not recommended. Consider using a bundle in the',
                'src directory or generate a new bundle then run this command.',
                '',
            ), 'bg=yellow;fg=black'));
        }

        $commandPath = dirname($reflect->getFileName()) . '/Command';

        if (!is_dir($commandPath)) {
            $filesystem->mkdir($commandPath);
        }

        $nameParts = explode(':', strtolower($input->getOption('name')));
        $filename  = '';
        foreach ($nameParts as $name) {
            $filename .= ucfirst($name);
        }
        $filename .= 'Command.php';
        $filesystem->touch($commandPath . '/' . $filename);

        $skeletonFile = __DIR__ . '/../../Resources/skeleton/Command/DefaultCommand.php';
        $skeletonContents = file_get_contents($skeletonFile);
        $contents = strtr($skeletonContents, array(
            '%namespace%'          => $reflect->getNamespaceName() . '\Command',
            '%command.class_name%' => str_replace('.php','',$filename),
            '%name%'               => strtolower($input->getOption('name')),
            '%description%'        => $input->getOption('description'),
            '%help%'               => '',
        ));
        file_put_contents($commandPath . '/' . $filename, $contents);

        $output->writeln(array(
            '',
            '<info>Command has been created</info>',
            '',
        ));
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
