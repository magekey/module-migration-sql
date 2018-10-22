<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\MigrationSql\Console\Command;

use Symfony\Component\Console\Command\Command;
use MageKey\MigrationSql\Model\Migration\Trigger as MigrationTrigger;

abstract class AbstractMigrationTriggerCommand extends Command
{
    /**
     * @var MigrationTrigger
     */
    protected $migrationTrigger;

    /**
     * @param MigrationTrigger $migrationTrigger
     */
    public function __construct(
        MigrationTrigger $migrationTrigger
    ) {
        $this->migrationTrigger = $migrationTrigger;
        parent::__construct();
    }
}
