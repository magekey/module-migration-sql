<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\MigrationSql\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use MageKey\MigrationSql\Model\Migration\Setup\Installer;

class MigrationSetupUpgradeCommand extends Command
{
    /**
     * @var Installer
     */
    protected $installer;

    /**
     * @param Installer $installer
     */
    public function __construct(
        Installer $installer
    ) {
        $this->installer = $installer;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migration:setup:upgrade')
            ->setDescription('Migration setup upgrade.');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->installer->install();
        $output->writeln('<info>Done</info>');
    }
}
