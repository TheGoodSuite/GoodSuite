<?php

namespace Good\Memory;

interface PropertyVisitor
{
	public function visitReferenceProperty($name, $datatypeName, $dirty, $null, 
													\Good\Manners\Storable $value = null);
	public function visitTextProperty($name, $dirty, $null, $value);
	public function visitIntProperty($name, $dirty, $null, $value);
	public function visitFloatProperty($name, $dirty, $null, $value);
	public function visitDatetimeProperty($name, $dirty, $null, $value);
}

?>