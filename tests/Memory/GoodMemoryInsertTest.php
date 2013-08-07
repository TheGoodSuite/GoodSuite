<?php

require_once dirname(__FILE__) . '/../Manners/GoodMannersInsertTest.php';

/** 
 * @runTestsInSeparateProcesses
 */
class GoodMemoryInsertTest extends GoodMannersInsertTest
{
    public function getNewDb()
    {
        require dirname(__FILE__) . '/dbconfig.php';
        
        return new \Good\Memory\Database\MySQL($dbconfig['name'], $dbconfig['host'], 
                                    $dbconfig['port'], $dbconfig['user'], $dbconfig['pass'], '');
    }
    
    public function getNewStore()
    {
        return new \Good\Memory\SQLStore($this->getNewDb());
    }
}

?>