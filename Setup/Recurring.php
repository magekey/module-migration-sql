<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\MigrationSql\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

use MageKey\MigrationSql\Model\Migration\Setup\Installer;

class Recurring implements InstallSchemaInterface
{
    /**
     * @var Installer
     */
    private $installer;

    /**
     * @param Installer $installer
     */
    public function __construct(
        Installer $installer
    ) {
        $this->installer = $installer;
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->installer->install();
    }
}
