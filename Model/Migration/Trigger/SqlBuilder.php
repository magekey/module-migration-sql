<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\MigrationSql\Model\Migration\Trigger;

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

        $documents = [];
        $sqlArr = [];
        foreach ($records as $record) {
            if (!$this->documentResolver->isDocumentExists($record['document'])) {
                continue;
            }
            $recordFieldName = $this->documentResolver->getDocumentRecordFieldName($record['document']);
            if (!$recordFieldName) {
                continue;
            }
            if ($sql = $this->getRecordSql($record, $recordFieldName)) {
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
     * @param string $recordFieldName
     * @return string|null
     */
    protected function getRecordSql(array $record, $recordFieldName)
    {
        $tableName = $this->connection->getTableName($record['document']);

        switch ($record['trigger_id']) {
            case MigrationTriggerResource::EVENT_INSERT:
            case MigrationTriggerResource::EVENT_UPDATE:
                $select = $this->connection
                    ->select()
                    ->from($tableName)
                    ->where($recordFieldName . ' = ?', $record['record_id']);
                $row = $this->connection->fetchRow($select);
                if (!empty($row)) {
                    $cols = [];
                    foreach ($row as $col => $val) {
                        $col = $this->connection->quoteIdentifier($col, true);
                        $val = $this->connection->quote($val);
                        $cols[$col] = $val;
                    }
                    $row = $cols;
                }
                break;
        }

        switch ($record['trigger_id']) {
            case MigrationTriggerResource::EVENT_INSERT:
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
            case MigrationTriggerResource::EVENT_UPDATE:
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
                        $this->connection->quoteIdentifier($recordFieldName),
                        $this->connection->quote($record['record_id'])
                    );
                    return $sql;
                }
                break;
            case MigrationTriggerResource::EVENT_DELETE:
                $sql = "DELETE FROM %s WHERE %s = %s;";
                $sql = sprintf(
                    $sql,
                    $this->connection->quoteIdentifier($tableName),
                    $this->connection->quoteIdentifier($recordFieldName),
                    $this->connection->quote($record['record_id'])
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
