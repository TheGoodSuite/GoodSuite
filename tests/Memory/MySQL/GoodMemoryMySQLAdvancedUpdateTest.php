<?php

require_once dirname(__FILE__) . '/../../Manners/GoodMannersAdvancedUpdateTest.php';

/**
 * @runTestsInSeparateProcesses
 * @group database
 * @group mysql
 */
class GoodMemoryMySQLAdvancedUpdateTest extends GoodMannersAdvancedUpdateTest
{
    public function getNewDb()
    {
        require dirname(__FILE__) . '/dbconfig.php';

        return new \Good\Memory\Database\MySQL($dbconfig['name'], $dbconfig['host'],
                                    $dbconfig['port'], $dbconfig['user'], $dbconfig['pass'], '');
    }

    public function getNewStorage()
    {
        return new \Good\Memory\SQLStorage($this->getNewDb());
    }

    public function truncateTable($table)
    {
        $db = $this->getNewDb();
        $db->query("TRUNCATE " . $table);
    }
}

?>
