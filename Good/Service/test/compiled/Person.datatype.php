<?php

include_once './GeneratedBaseClass.php'

class Person extends GeneratedBaseClass
{
	private $username;
	
	public function getUsername()
	{
		
		return $this->username;
	}
	
	public function setUsername($value)
	{
		
		$this->username = $value;
		
		notifyObservers();
		
		dirty();
	}
	
	private $firstName;
	
	public function getFirstName()
	{
		
		return $this->firstName;
	}
	
	public function setFirstName($value)
	{
		
		$this->firstName = $value;
		
		notifyObservers();
		
		dirty();
	}
	
	private $lastName;
	
	public function getLastName()
	{
		
		return $this->lastName;
	}
	
	public function setLastName($value)
	{
		
		$this->lastName = $value;
		
		notifyObservers();
		
		dirty();
	}
	
	private $password;
	
	protected function getPassword()
	{
		
		return $this->password;
	}
	
	protected function setPassword($value)
	{
		
		$this->password = $value;
		
		notifyObservers();
		
		dirty();
	}
	
	private $age;
	
	public function getAge()
	{
		
		return $this->age;
	}
	
	public function setAge($value)
	{
		
		$this->age = $value;
		
		notifyObservers();
		
		dirty();
	}
	
	private $grade;
	
	public function getGrade()
	{
		
		return $this->grade;
	}
	
	public function setGrade($value)
	{
		
		$this->grade = $value;
		
		notifyObservers();
		
		dirty();
	}
	
	private $relation;
	
	public function getRelation()
	{
		
		return $this->relation;
	}
	
	public function setRelation($value)
	{
		
		$this->relation = $value;
		
		notifyObservers();
		
		dirty();
	}
	
	private $home;
	
	public function getHome()
	{
		
		return $this->home;
	}
	
	public function setHome($value)
	{
		
		$this->home = $value;
		
		notifyObservers();
		
		dirty();
	}
	
}

?>