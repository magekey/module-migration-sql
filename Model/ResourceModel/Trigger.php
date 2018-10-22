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
    const FIELD_DOCUMENT = 'document';

    const FIELD_TRIGGER_ID = 'trigger_id';

    const FIELD_RECORD_ID = 'record_id';

    const FIELD_TIME = 'time';

    /**
     * Events
     */
    const EVENT_INSERT = 1;

    const EVENT_UPDATE = 2;

    const EVENT_DELETE = 3;

    /**
     * List of events available for trigger
     *
     * @var array
     */
    protected static $listOfEvents = [
        TriggerDDL::EVENT_INSERT => self::EVENT_INSERT,
        TriggerDDL::EVENT_UPDATE => self::EVENT_UPDATE,
        TriggerDDL::EVENT_DELETE => self::EVENT_DELETE
    ];

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, null);
    }

    /**
     * Retrieve list of events available for trigger
     *
     * @return array
     */
    public static function getListOfEvents()
    {
        return self::$listOfEvents;
    }
}
