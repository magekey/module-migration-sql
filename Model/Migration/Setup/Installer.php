<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\MigrationSql\Model\Migration\Setup;

use MageKey\MigrationSql\Model\Migration\Filesystem;
use MageKey\MigrationSql\Model\ResourceModel\Setup as MigrationSetupResource;

class Installer
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var SqlExecutor
     */
    protected $sqlExecutor;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param Filesystem $filesystem
     * @param SqlExecutor $sqlExecutor
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        Filesystem $filesystem,
        SqlExecutor $sqlExecutor
    ) {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->filesystem = $filesystem;
        $this->sqlExecutor = $sqlExecutor;
    }

    /**
     * Install
     *
     * @return void
     */
    public function install()
    {
        $migrationSetupTable = $this->resource->getTableName(MigrationSetupResource::TABLE_NAME);
        $select = $this->connection
            ->select()
            ->from($migrationSetupTable);

        $appliedMigrations = $this->connection->fetchCol($select);
        $newMigrations = [];
        $queries = "";

        $fileList = $this->filesystem->getMigrationSetupFiles();
        foreach ($fileList->toArray() as $file => $content) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);
            if (!in_array($migrationName, $appliedMigrations)) {
                $newMigrations[] = $migrationName;
                if (!empty($content)) {
                    $queries .= $content;
                }
            }
        }

        if (!empty($queries)) {
            $this->sqlExecutor->execute($queries);
        }

        if (!empty($newMigrations)) {
            $records = [];
            foreach (array_unique($newMigrations) as $name) {
                $records[] = ['name' => $name];
            }
            $this->connection->insertMultiple(
                $migrationSetupTable,
                $records
            );
        }
    }
}
