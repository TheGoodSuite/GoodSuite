<?php

namespace Good\Memory;

interface PropertyVisitor
{
	public function visitReferenceProperty($name, $datatypeName, $dirty, 
													\Good\Manners\Storable $value = null);
	public function visitTextProperty($name, $dirty, $value);
	public function visitIntProperty($name, $dirty, $value);
	public function visitFloatProperty($name, $dirty, $value);
	public function visitDatetimeProperty($name, $dirty, $value);
}

?>