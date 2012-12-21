<?php

class GoodMemorySQLPostponedForeignKey
{
	private $referer;
	private $field;
	private $foreigner;
	
	public function __construct($referer, $field, $foreigner)
	{
		$this->referer = $referer;
		$this->field = $field;
		$this->foreigner = $foreigner;
	}
	
	public function doNow()
	{
		// Is there maybe a nicer way to do this?
		$field = 'set' . ucfirst($this->field);
		$this->referer->$field($foreigner->getId());
	}
}

?>