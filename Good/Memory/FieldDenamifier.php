<?php

namespace Good\Memory;

use Good\Manners\Storable;

class FieldDenamifier implements PropertyVisitor
{
	private $store;
	private $data;
	private $out;
	
	public function __construct($store)
	{
		$this->store = $store;
	}
	
	public function denamifyFields(array $data, Storable $storable)
	{
		$this->data = $data;
		$this->store->setCurrentPropertyVisitor($this);
		$this->out = array();
		
		$storable->acceptStorableVisitor($this->store);
		
		return $this->out;
	}
    
	private function visitProperty($name)
	{
		$fieldNamified = $this->store->fieldNamify($name);
		
		if (array_key_exists($fieldNamified, $this->data))
		{
			$this->out[$name] = $this->data[$fieldNamified];
		}
	}
	
	public function visitReferenceProperty($name, $datatypeName, $dirty, 
													Storable $value = null)
	{
		$this->visitProperty($name);
	}
	
	public function visitTextProperty($name, $dirty, $value)
	{
		$this->visitProperty($name);
	}
	
	public function visitIntProperty($name, $dirty, $value)
	{
		$this->visitProperty($name);
	}
	
	public function visitFloatProperty($name, $dirty, $value)
	{
		$this->visitProperty($name);
	}
	
	public function visitDatetimeProperty($name, $dirty, $value)
	{
		$this->visitProperty($name);
	}
}