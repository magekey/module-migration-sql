<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\MigrationSql\Model\ResourceModel;

use Magento\Framework\DB\Ddl\Trigger as TriggerDDL;

class Trigger extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Table name
     */
    const TABLE_NAME = 'migration_trigger';

    /**
     * Fields
     */
    const FIELD_ID = 'id';

    const FIELD_DOCUMENT = 'document';

    const FIELD_TRIGGER = 'trigger';

    const FIELD_COLUMN = 'column';

    const FIELD_VALUE = 'value';

    const FIELD_TIME = 'time';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::FIELD_ID);
    }
}
