<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\MigrationSql\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class MigrationTriggerDeleteCommand extends AbstractMigrationTriggerCommand
{
    /**
     * Arguments
     */
    const INPUT_KEY_DOCUMENT = 'document';
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migration:trigger:delete')
            ->setDescription('Delete migration triggers.')
            ->setDefinition([
                new InputArgument(
                    self::INPUT_KEY_DOCUMENT,
                    InputArgument::IS_ARRAY,
                    'Table names'
                ),
            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->migrationTrigger
            ->delete(
                $input->getArgument(self::INPUT_KEY_DOCUMENT)
            );
    }
}
