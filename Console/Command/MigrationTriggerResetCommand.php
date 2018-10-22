<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\MigrationSql\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationTriggerResetCommand extends AbstractMigrationTriggerCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migration:trigger:reset')
            ->setDescription('Clear migration changes.');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->migrationTrigger->reset();
        $output->writeln('<info>Trigger changes have been removed</info>');
    }
}
