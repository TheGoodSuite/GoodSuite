<?php

require_once dirname(__FILE__) . '/../../Manners/GoodMannersPaginationTest.php';

/**
 * @runTestsInSeparateProcesses
 * @group database
 * @group sqlite
 */
class GoodMemorySQLitePaginationTest extends GoodMannersPaginationTest
{
    public function getNewDb()
    {
        require dirname(__FILE__) . '/dbconfig.php';

        return new \Good\Memory\Database\SQLite($dbconfig['filename']);
    }

    public function getNewStorage()
    {
        return new \Good\Memory\SQLStorage($this->getNewDb());
    }

    public function truncateTable($table)
    {
        $db = $this->getNewDb();
        $db->query("DELETE FROM " . $table);
    }
}

?>
