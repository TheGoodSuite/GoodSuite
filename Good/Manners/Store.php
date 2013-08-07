<?php

namespace Good\Manners;

abstract class Store implements StorableVisitor
{
	private $validationToken;
	
	public function __construct()
	{
		$this->validationToken = new ValidationToken();
	}
	
	public function __destruct()
	{
		$this->flush();
	}
	
	protected function invalidate()
	{
		$this->validationToken->invalidate();
		$this->validationToken = new ValidationToken();
	}
	
	abstract public function insert(Storable $storable);
	abstract public function modifyAny(Condition $condition, Storable $modifications);
	abstract public function getCollection(Condition $condition, Resolver $resolver);
	
	abstract public function flush();
	
	abstract public function dirtyStorable(Storable $storable);
	
	abstract public function visitReferenceProperty($name, $datatypeName, $dirty, Storable $value = null);
	abstract public function visitTextProperty($name, $dirty, $value);
	abstract public function visitIntProperty($name, $dirty, $value);
	abstract public function visitFloatProperty($name, $dirty, $value);
	abstract public function visitDatetimeProperty($name, $dirty, $value);
	
	
}

?>