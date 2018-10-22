<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\MigrationSql\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationTriggerStatusCommand extends AbstractMigrationTriggerCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migration:trigger:status')
            ->setDescription('Get migration trigger status.');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $documents = $this->migrationTrigger->getActiveDocuments();
        if (empty($documents)) {
            $output->writeln('<comment>' . __('Documents not found.') . '</comment>');
        } else {
            $output->writeln('<comment>' . __('Documents:') . '</comment>');
            foreach ($documents as $document) {
                $output->writeln('<info>  ' . $document . '</info>');
            }
        }
    }
}
