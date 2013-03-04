<?php

namespace Good\Manners;

require_once 'Resolver.php';

abstract class AbstractResolver implements Resolver
{
	const ORDER_DIRECTION_ASC = 0;
	const ORDER_DIRECTION_DESC = 1;

	protected $root;
	
	private $orderCount = 0;
	
	public function __construct(AbstractResolver $root = null)
	{
		if ($root == null)
		{
			$this->root = $this;
		}
		else
		{
			$this->root = $root;
		}
	}
	
	public function drawOrderTicket()
	{
		$count = $this->root->orderCount;
		$this->root->orderCount++;
		
		return $count;
	}
}

?>