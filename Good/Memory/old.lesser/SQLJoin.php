<?php

class GoodMemorySQLJoin
{
	public $tableNumberOrigin;
	public $fieldNameOrigin;
	public $tableNameDestination;
	public $tableNumberDestination;
	
	public function __construct($tableNumberOrigin, $fieldNameOrigin, $tableNameDestination, $tableNumberDestination)
	{
		$this->tableNumberOrigin = $tableNumberOrigin;
		$this->fieldNameOrigin = $fieldNameOrigin;
		$this->tableNameDestination = $tableNameDestination;
		$this->tableNumberDestination = $tableNumberDestination;
	}
}

?>