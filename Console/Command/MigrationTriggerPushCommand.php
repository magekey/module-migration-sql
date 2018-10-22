<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\MigrationSql\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class MigrationTriggerPushCommand extends AbstractMigrationTriggerCommand
{
    /**
     * Arguments
     */
    const INPUT_KEY_NAME = 'name';

    const INPUT_KEY_MODULE = 'module';
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migration:trigger:push')
            ->setDescription('Push migration trigger to migration sql.')
            ->setDefinition([
                new InputArgument(
                    self::INPUT_KEY_NAME,
                    InputArgument::OPTIONAL,
                    'Migration name'
                ),
                new InputArgument(
                    self::INPUT_KEY_MODULE,
                    InputArgument::OPTIONAL,
                    'Module name [Vendor_Module]'
                )
            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->migrationTrigger->push(
            $input->getArgument(self::INPUT_KEY_NAME),
            $input->getArgument(self::INPUT_KEY_MODULE)
        );
    }
}
