<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\MigrationSql\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationTriggerClearCommand extends AbstractMigrationTriggerCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migration:trigger:clear')
            ->setDescription('Clear migration trigger.');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->migrationTrigger->clear();
    }
}
