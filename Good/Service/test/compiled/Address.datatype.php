<?php

include_once './GeneratedBaseClass.php'

class Address extends GeneratedBaseClass
{
	private $street;
	
	public function getStreet()
	{
		
		return $this->street;
	}
	
	public function setStreet($value)
	{
		
		$this->street = $value;
		
		notifyObservers();
		
		dirty();
	}
	
	private $number;
	
	public function getNumber()
	{
		
		return $this->number;
	}
	
	public function setNumber($value)
	{
		
		$this->number = $value;
		
		notifyObservers();
		
		dirty();
	}
	
	private $addition;
	
	public function getAddition()
	{
		
		return $this->addition;
	}
	
	public function setAddition($value)
	{
		
		$this->addition = $value;
		
		notifyObservers();
		
		dirty();
	}
	
	private $city;
	
	public function getCity()
	{
		
		return $this->city;
	}
	
	public function setCity($value)
	{
		
		$this->city = $value;
		
		notifyObservers();
		
		dirty();
	}
	
	private $state;
	
	public function getState()
	{
		
		return $this->state;
	}
	
	public function setState($value)
	{
		
		$this->state = $value;
		
		notifyObservers();
		
		dirty();
	}
	
	private $country;
	
	public function getCountry()
	{
		
		return $this->country;
	}
	
	public function setCountry($value)
	{
		
		$this->country = $value;
		
		notifyObservers();
		
		dirty();
	}
	
}

?>