<?php

require_once dirname(__FILE__) . '/../../Manners/GoodMannersAdvancedUpdateTest.php';

/** 
 * @runTestsInSeparateProcesses
 */
class GoodMemoryAdvancedUpdateTest extends GoodMannersAdvancedUpdateTest
{
    public function getNewDb()
    {
        require dirname(__FILE__) . '/dbconfig.php';
        
        return new \Good\Memory\Database\SQLite($dbconfig['filename']);
    }
    
    public function getNewStorage()
    {
        $a = new \Good\Memory\SQLStorage($this->getNewDb());
        
        return $a;
    }
    
    public function truncateTable($table)
    {
        $db = $this->getNewDb();
        $db->query("DELETE FROM " . $table);
    }
}

?>