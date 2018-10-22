<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\MigrationSql\Model\Migration;

use Magento\Framework\DB\Ddl\Trigger as TriggerDDL;
use Magento\Framework\DB\Ddl\TriggerFactory as TriggerDDLFactory;
use Magento\Framework\DB\ExpressionConverter;

use MageKey\MigrationSql\Model\ResourceModel\Trigger as MigrationTriggerResource;
use MageKey\MigrationSql\Model\Migration\Filesystem;
use MageKey\MigrationSql\Model\Migration\Trigger\DocumentResolver;
use MageKey\MigrationSql\Model\Migration\Trigger\SqlBuilder;
use MageKey\MigrationSql\Model\ResourceModel\Setup as MigrationSetupResource;

class Trigger
{
    /**
     * Migration trigger prefix
     */
    const TRIGGER_PREFIX = 'm__';

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var TriggerDDLFactory
     */
    protected $ddlTriggerFactory;

    /**
     * @var DocumentResolver
     */
    protected $documentResolver;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var SqlBuilder
     */
    protected $sqlBuilder;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param TriggerDDLFactory $ddlTriggerFactory
     * @param DocumentResolver $documentResolver
     * @param Filesystem $filesystem
     * @param SqlBuilder $sqlBuilder
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        TriggerDDLFactory $ddlTriggerFactory,
        DocumentResolver $documentResolver,
        Filesystem $filesystem,
        SqlBuilder $sqlBuilder
    ) {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->ddlTriggerFactory = $ddlTriggerFactory;
        $this->documentResolver = $documentResolver;
        $this->filesystem = $filesystem;
        $this->sqlBuilder = $sqlBuilder;
    }

    /**
     * Create triggers
     *
     * @param array $documents
     * @return $this
     */
    public function create(array $documents = [])
    {
        $documents = $this->documentResolver->resolveDocuments($documents);
        foreach ($documents as $document) {
            $this->createDocumentTriggers($document);
        }

        return $this;
    }

    /**
     * Delete triggers
     *
     * @param array $documents
     * @return $this
     */
    public function delete(array $documents = [])
    {
        $documents = $this->documentResolver->resolveDocuments($documents);
        foreach ($documents as $document) {
            $this->removeDocumentTriggers($document);
        }

        return $this;
    }

    /**
     * Push changes
     *
     * @param string|null $name
     * @param string|null $module
     * @return string|null
     */
    public function push($name = null, $module = null)
    {
        $this->filesystem->assertMigration($name, $module);
        $content = $this->sqlBuilder->build();
        if (empty($content)) {
            return null;
        }
        $file = $this->filesystem->getMigrationFile($name, $module, true);
        $this->filesystem->getDirectory()->writeFile($file, $content, "a+");

        $migrationName = pathinfo($file, PATHINFO_FILENAME);
        $this->connection->insertOnDuplicate(
            $this->resource->getTableName(MigrationSetupResource::TABLE_NAME),
            ['name' => $migrationName]
        );
        $this->reset();

        return $file;
    }

    /**
     * Reset changes
     *
     * @return void
     */
    public function reset()
    {
        $this->connection->truncateTable(
            $this->resource->getTableName(MigrationTriggerResource::TABLE_NAME)
        );
    }

    /**
     * Get active documents
     *
     * @return array
     */
    public function getActiveDocuments()
    {
        $result = [];

        $triggers = $this->getAvailableTriggers();
        $documents = $this->connection->listTables();
        foreach ($documents as $document) {
            foreach (TriggerDDL::getListOfEvents() as $event) {
                $triggerName = $this->getAfterEventTriggerName($document, $event);
                if (isset($triggers[$triggerName])) {
                    $result[] = $triggers[$triggerName];
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Get available triggers
     *
     * @return array
     */
    public function getAvailableTriggers()
    {
        $triggers = [];

        $records = $this->connection->fetchAll('SHOW TRIGGERS');
        foreach ($records as $record) {
            $triggers[$record['Trigger']] = $record['Table'];
        }

        return $triggers;
    }

    /**
     * Create document triggers
     *
     * @param string $document
     * @return void
     */
    public function createDocumentTriggers($document)
    {
        $recordFieldName = $this->documentResolver->getDocumentPrimaryColumnName($document);
        if (!$recordFieldName) {
            return;
        }

        foreach (TriggerDDL::getListOfEvents() as $event) {
            $triggerName = $this->getAfterEventTriggerName($document, $event);
            $triggerDDL = $this->ddlTriggerFactory->create()
                ->setName($triggerName)
                ->setTime(TriggerDDL::TIME_AFTER)
                ->setEvent($event)
                ->setTable($this->resource->getTableName($document));

            $triggerDDL->addStatement($this->buildStatement($document, $event, $recordFieldName));

            $this->connection->dropTrigger($triggerDDL->getName());
            $this->connection->createTrigger($triggerDDL);
        }
    }

    /**
     * Remove document triggers
     *
     * @param string $document
     * @return void
     */
    public function removeDocumentTriggers($document)
    {
        foreach (TriggerDDL::getListOfEvents() as $event) {
            $triggerName = $this->getAfterEventTriggerName($document, $event);
            $this->connection->dropTrigger($triggerName);
        }
    }

    /**
     * Build trigger statement
     *
     * @param string $document
     * @param string $event
     * @param string $recordFieldName
     * @return string
     */
    protected function buildStatement($document, $event, $recordFieldName)
    {
        switch ($event) {
            case TriggerDDL::EVENT_INSERT:
            case TriggerDDL::EVENT_UPDATE:
            case TriggerDDL::EVENT_DELETE:
                $trigger = "INSERT IGNORE INTO %s (%s) VALUES (%s);";
                break;

            default:
                return '';
        }

        $trigger = sprintf(
            $trigger,
            $this->connection->quoteIdentifier($this->resource->getTableName(MigrationTriggerResource::TABLE_NAME)),
            implode(', ', [
                $this->connection->quoteIdentifier(MigrationTriggerResource::FIELD_DOCUMENT),
                $this->connection->quoteIdentifier(MigrationTriggerResource::FIELD_TRIGGER),
                $this->connection->quoteIdentifier(MigrationTriggerResource::FIELD_COLUMN),
                $this->connection->quoteIdentifier(MigrationTriggerResource::FIELD_VALUE),
            ]),
            implode(', ', [
                $this->connection->quote($document),
                $this->connection->quote($event),
                $this->connection->quote($recordFieldName),
                $this->connection->quoteIdentifier('%s.' . $recordFieldName),
            ])
        );

        switch ($event) {
            case TriggerDDL::EVENT_INSERT:
            case TriggerDDL::EVENT_UPDATE:
                $trigger = sprintf($trigger, 'NEW');
                break;
            case TriggerDDL::EVENT_DELETE:
                $trigger = sprintf($trigger, 'OLD');
                break;

            default:
                return '';
        }

        return $trigger;
    }

    /**
     * Build an "after" event trigger name
     *
     * @param string $document
     * @param string $event
     * @return string
     */
    private function getAfterEventTriggerName($document, $event)
    {
        $triggerName = static::TRIGGER_PREFIX
            . $this->resource->getTableName($document)
            . '_' . TriggerDDL::TIME_AFTER
            . '_' . $event;
        return strtolower(ExpressionConverter::shortenEntityName($triggerName, static::TRIGGER_PREFIX));
    }
}
