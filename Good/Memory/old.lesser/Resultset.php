<?php

require_once dirname(__FILE__) . '/Database/Database.php';

// TODO: Make this class strongly typed / typesafe
//       (idea: compile this class, use visitor pattern with this as Visitor and
//              Storable as Visitable and a compiled Visitor template to generate
//				the child classes, and finally use different output functions for
//				each Storable subclass we can return)

// TODO: Make sure that only the current row is loaded into memory at once
//       (to do this, I'll probably need to modify how I get results from
//		  my Database classes, as currently the system can't handle two
//		  simultaneous queries and that's what's needed for this wanted
//		  behaviour)

class GoodMemoryResultSet
{
	private $results;
	
	public function __construct(Database $db, GoodMannersStore $store, array $classMap)
	{
		$results = array();
		
		while ($row = $db->getNextResult())
		{
			foreach($row)
			{
				
			}
		}
	}
}

?>