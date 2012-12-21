<?php

interface GoodMemoryPropertyVisitor
{
	public function visitReferenceProperty($name, $datatypeName, $dirty, $null, 
													GoodMannersStorable $value = null);
	public function visitTextProperty($name, $dirty, $null, $value);
	public function visitIntProperty($name, $dirty, $null, $value);
	public function visitFloatProperty($name, $dirty, $null, $value);
}

?>