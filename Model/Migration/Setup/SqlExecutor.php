<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\MigrationSql\Model\Migration\Setup;

use Magento\Framework\ShellInterface;
use Magento\Framework\Exception\LocalizedException;

use MageKey\MigrationSql\Model\Migration\Filesystem;

class SqlExecutor
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\Framework\App\Shell
     */
    protected $shell;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var array
     */
    protected $config;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\App\Shell $shell
     * @param Filesystem $filesystem
     * @param array $config
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\Shell $shell,
        Filesystem $filesystem,
        array $config = []
    ) {
        $this->connection = $resource->getConnection();
        $this->shell = $shell;
        $this->filesystem = $filesystem;

        if (!isset($config['command'])) {
            $config['command'] = 'export MYSQL_PWD=:pass && mysql -h :host -u :user :dbname < :file';
        }
        $this->config = array_merge_recursive($this->connection->getConfig(), $config);
    }

    /**
     * Exec queries
     *
     * @param string queries
     * @return void
     * @throws LocalizedException
     */
    public function execute($queries)
    {
        $file = $this->filesystem->createMigrationCombinedFile($queries);
        if (!is_file($file)) {
            throw new LocalizedException(
                __('Combined migration file not found.')
            );
        }

        $command = str_replace(
            [
                ':host',
                ':user',
                ':pass',
                ':dbname',
                ':file',
            ],
            [
                $this->config['host'],
                $this->config['username'],
                $this->config['password'],
                $this->config['dbname'],
                $file
            ],
            $this->config['command']
        );

        try {
            $this->shell->execute($command);
            unlink($file);
        } catch (\Exception $e) {
            unlink($file);
            if ($prev = $e->getPrevious()) {
                throw $prev;
            }
            throw $e;
        }
    }
}
