<?
function foo(&$bar = false)
{
	$object1 = array('k' => 1, 'l' => 2, 'm' => 3);
	
	echo "{$object1['q']} <br /><br />";
	
	if ($bar)
	{
		echo "true <br /><br /><br />";
	}
	else
	{
		echo "false <br /><br /><br />";
	}
}

$bool = true;

foo($bool);
foo();
?>