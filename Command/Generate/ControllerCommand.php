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
class ControllerCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('name', '', InputOption::VALUE_REQUIRED, 'Controller Name'),
                new InputOption('bundle', '', InputOption::VALUE_REQUIRED, 'Name of bundle to place this controller in'),
            ))
            ->setName('generate:controller')
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

        $dialog->writeSection($output, 'Biga Controller Generator');

        // name
        $output->writeln(array(
            '',
            'This is the name on the controller, for example given the name "Default"',
            'this will create a controller named "DefaultController.php"',
            '',
        ));

        $name = $dialog->askAndValidate($output, $dialog->getQuestion('Controller Name', $input->getOption('name')), function($value) use($validator) {
            $errors = $validator->validateValue($value, new Assert\NotBlank());
            if (count($errors)) {
                throw new \InvalidArgumentException(trim(str_replace(":\n", ":", (string) $errors)));
            }
            return $value;
        }, false, $input->getOption('name'));
        $input->setOption('name', $name);

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

        $controllerPath = dirname($reflect->getFileName()) . '/Controller';

        if (!is_dir($controllerPath)) {
            $filesystem->mkdir($controllerPath);
        }

        $filename = ucfirst(strtolower($input->getOption('name')));
        $filename .= 'Controller.php';
        $filesystem->touch($controllerPath . '/' . $filename);

        $skeletonFile = __DIR__ . '/../../Resources/skeleton/Controller/DefaultController.php';
        $skeletonContents = file_get_contents($skeletonFile);
        $contents = strtr($skeletonContents, array(
            '%namespace%'          => $reflect->getNamespaceName() . '\Controller;',
            '%controller.class_name%' => str_replace('.php','',$filename),
        ));
        file_put_contents($controllerPath . '/' . $filename, $contents);

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
