<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\MigrationSql\Model\Migration\Trigger;

use Magento\Framework\Exception\LocalizedException;

class DocumentResolver
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var array
     */
    protected $ignored;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param array $ignored
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        array $ignored = []
    ) {
        $this->connection = $resource->getConnection();
        $this->ignored = $ignored;
    }

    /**
     * Check if document exists
     *
     * @param string $document
     * @return bool
     */
    public function isDocumentExists($document)
    {
        return $this->connection->isTableExists($document);
    }

    /**
     * Retrieve document primary column name
     *
     * @param string $document
     * @return string|null
     */
    public function getDocumentPrimaryColumnName($document)
    {
        $describe = $this->connection->describeTable($document);
        foreach ($describe as $column) {
            if ($column['PRIMARY']) {
                return $column['COLUMN_NAME'];
            }
        }

        return null;
    }

    /**
     * Resolve documents
     *
     * @param array $documents
     * @return array
     * @throws LocalizedException
     */
    public function resolveDocuments(array $documents)
    {
        $documentsList = $this->connection->listTables();
        if (empty($documents)) {
            $documents = $documentsList;
        } else {
            $notFound = array_diff($documents, $documentsList);
            if (!empty($notFound)) {
                throw new LocalizedException(
                    __('Documents not found: %1', implode(',', $notFound))
                );
            }
        }

        return array_diff($documents, $this->ignored);
    }
}
