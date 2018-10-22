<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\MigrationSql\Model\Migration\Trigger;

use Magento\Framework\DB\Ddl\Trigger as TriggerDDL;

use MageKey\MigrationSql\Model\Migration\Trigger\DocumentResolver;
use MageKey\MigrationSql\Model\ResourceModel\Trigger as MigrationTriggerResource;

class SqlBuilder
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var DocumentResolver
     */
    protected $documentResolver;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param DocumentResolver $documentResolver
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        DocumentResolver $documentResolver
    ) {
        $this->connection = $resource->getConnection();
        $this->documentResolver = $documentResolver;
    }

    /**
     * Build sql
     *
     * @return string
     */
    public function build()
    {
        $select = $this->connection
            ->select()
            ->from(
                $this->connection->getTableName(MigrationTriggerResource::TABLE_NAME)
            );
        $records = $this->connection->fetchAll($select);
        if (empty($records)) {
            return "";
        }

        $documents = [];
        $sqlArr = [];
        foreach ($records as $record) {
            if (!$this->documentResolver->isDocumentExists($record['document'])) {
                continue;
            }
            if ($sql = $this->getRecordSql($record)) {
                $documents[] = $record['document'];
                $sqlArr[] = $sql;
            }
        }

        return $this->formatOutput($documents, $sqlArr);
    }

    /**
     * Get record sql
     *
     * @param array $record
     * @return string|null
     */
    protected function getRecordSql(array $record)
    {
        $tableName = $this->connection->getTableName($record['document']);
        $triggerEvent = strtoupper($record['trigger']);

        switch ($triggerEvent) {
            case TriggerDDL::EVENT_INSERT:
            case TriggerDDL::EVENT_UPDATE:
                $select = $this->connection
                    ->select()
                    ->from($tableName)
                    ->where($record['column'] . ' = ?', $record['value']);
                $row = $this->connection->fetchRow($select);
                if (!empty($row)) {
                    $cols = [];
                    foreach ($row as $col => $val) {
                        $col = $this->connection->quoteIdentifier($col, true);
                        $val = ($val === null ? 'NULL' : $this->connection->quote($val));
                        $cols[$col] = $val;
                    }
                    $row = $cols;
                }
                break;
        }

        switch ($triggerEvent) {
            case TriggerDDL::EVENT_INSERT:
                if (!empty($row)) {
                    $sql = "INSERT INTO %s (%s) VALUES (%s);";
                    $sql = sprintf(
                        $sql,
                        $this->connection->quoteIdentifier($tableName),
                        implode(', ', array_keys($row)),
                        implode(', ', array_values($row))
                    );
                    return $sql;
                }
                break;
            case TriggerDDL::EVENT_UPDATE:
                if (!empty($row)) {
                    $set = [];
                    foreach ($row as $col => $val) {
                        $set[] = $col . ' = ' . $val;
                    }

                    $sql = "UPDATE %s SET %s WHERE %s = %s;";
                    $sql = sprintf(
                        $sql,
                        $this->connection->quoteIdentifier($tableName),
                        implode(', ', $set),
                        $this->connection->quoteIdentifier($record['column']),
                        $this->connection->quote($record['value'])
                    );
                    return $sql;
                }
                break;
            case TriggerDDL::EVENT_DELETE:
                $sql = "DELETE FROM %s WHERE %s = %s;";
                $sql = sprintf(
                    $sql,
                    $this->connection->quoteIdentifier($tableName),
                    $this->connection->quoteIdentifier($record['column']),
                    $this->connection->quote($record['value'])
                );
                return $sql;
        }

        return null;
    }

    /**
     * Format output
     *
     * @param array $documents
     * @param array $sqlArr
     * @return string
     */
    protected function formatOutput(array $documents, array $sqlArr)
    {
        $date = date('Y-m-d H:i:s');
        sort($documents);
        $documents = implode(', ', array_unique($documents));

        $comment = <<<EOF
/*
 * Date: $date
 * Documents: $documents
 */
EOF;
        array_unshift($sqlArr, $comment);
        return implode("\r\n", $sqlArr) . "\r\n\r\n";
    }
}
