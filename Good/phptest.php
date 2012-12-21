<?php

function bla(&$i = 11)
{
	$i--;
	
	if ($i == 0)
	{
		echo "Liftoff!<br />";
	}
	else if ($i > 0)
	{
		echo $i . "...<br />";
		bla($i);
	}
	
	if ($i == 0)
	{
		echo "Liftoff was successfull.";
	}
}

bla();

?>