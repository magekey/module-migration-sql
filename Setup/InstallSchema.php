<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\MigrationSql\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $connection = $setup->getConnection();

        /**
         * Create table 'migration_setup'
         */
        $table = $connection->newTable(
            $installer->getTable('migration_setup')
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'primary' => true],
            'Migration Name'
        )->addColumn(
            'time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Migration Time'
        )->setComment(
            'Migration Setup Table'
        );
        $connection->createTable($table);

        /**
         * Create table 'migration_trigger'
         */
        $table = $connection->newTable(
            $installer->getTable('migration_trigger')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'primary' => true, 'identity' => true, 'unsinged' => true],
            'Id'
        )->addColumn(
            'document',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Document name'
        )->addColumn(
            'trigger',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['nullable' => false],
            'Trigger'
        )->addColumn(
            'column',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['nullable' => false],
            'Column'
        )->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Value'
        )->addColumn(
            'time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Action Time'
        )->setComment(
            'Migration Trigger Table'
        );
        $connection->createTable($table);

        $installer->endSetup();
    }
}
