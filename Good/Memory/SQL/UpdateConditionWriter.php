<?php

namespace Good\Memory\SQL;

use Good\Memory\Database\Database;
use Good\Memory\SQLStorage;
use Good\Manners\Condition;

class UpdateConditionWriter
{
    private $storage;
    private $db;

    private $condition;

    public function __construct(SQLStorage $storage, Database $db)
    {
        $this->storage = $storage;
        $this->db = $db;
    }

    public function getCondition()
    {
        return $this->condition;
    }

    public function writeCondition(Condition $condition,
                                   $rootTableName,
                                   $updatingTableNumber)
    {
        $selecter = new Selecter($this->storage, $this->db, 0);

        $select  = 'SELECT `t' . $updatingTableNumber .'`.`id`';
        $select .= ' ' . $selecter->writeQueryWithoutSelect($rootTableName, $condition);

        $this->condition = '`id` IN (' . $select . ')';
    }
}

?>
