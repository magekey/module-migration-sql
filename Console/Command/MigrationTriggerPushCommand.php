<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\MigrationSql\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrationTriggerPushCommand extends AbstractMigrationTriggerCommand
{
    /**
     * Arguments
     */
    const INPUT_ARGUMENT_NAME = 'name';

    const INPUT_OPTION_MODULE = 'module';
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migration:trigger:push')
            ->setDescription('Push migration trigger to migration sql.')
            ->setDefinition([
                new InputArgument(
                    self::INPUT_ARGUMENT_NAME,
                    InputArgument::OPTIONAL,
                    'Migration name'
                ),
                new InputOption(
                    self::INPUT_OPTION_MODULE,
                    'm',
                    InputOption::VALUE_OPTIONAL,
                    'Module Name [Vendor_Module]'
                )
            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $this->migrationTrigger->push(
            $input->getArgument(self::INPUT_ARGUMENT_NAME),
            $input->getOption(self::INPUT_OPTION_MODULE)
        );

        if ($file) {
            $output->writeln('<info>Changes pushed to sql file:</info> <comment>' . $file . '</comment>');
        } else {
            $output->writeln('<comment>Nothing to push</comment>');
        }
    }
}
