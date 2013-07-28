<?php

require_once dirname(__FILE__) . '/../Manners/GoodMannersAdvancedUpdateTest.php';

/** 
 * @runTestsInSeparateProcesses
 */
class GoodMemoryAdvancedUpdateTest extends GoodMannersAdvancedUpdateTest
{
	public function getNewDb()
	{
		require dirname(__FILE__) . '/dbconfig.php';
		
		return new \Good\Memory\Database\MySQL($dbconfig['name'], $dbconfig['host'], 
									$dbconfig['port'], $dbconfig['user'], $dbconfig['pass'], '');
	}
	
	public function getNewStore()
	{
		return new GoodMemorySQLStore($this->getNewDb());
	}
}

?>