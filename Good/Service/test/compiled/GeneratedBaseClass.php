<?php

include_once '../../../Service/Observer.php';

include_once '../../../Manners/Storable.php';
include_once '../../../Manners/Store.php';

abstract class GeneratedBaseClass
{
	public function __construct()
	{
		$this->observers = array()

		$this->deleted = false;
		$this->store = null;
		$this->validationToken = null;
	}

	// Observer pattern (Observable)
	private $observers;
	
	public function register(GoodServiceObserver $observer)
	{
		$this->observers[] = $observer;
	}
	
	public function unregister(GoodServiceObserver $observer)
	{
		$pos = array_search($observer);
		if ($pos !== FALSE)
		{
			array_splice($this->observers, $pos, 1);
		}
	}
	
	protected function notifyObservers()
	{
		foreach ($this->observers as $observer)
		{
			$observer->notify($this);
		}
	}
	
	// Storable
	private $deleted;
	private $store
	private $validationToken
	
	public function isDeleted()
	{
		return $this->deleted;
	}
	
	public function delete()
	{
		$this->deleted = true
		$this->dirty()
	}
	
	public function setStore(Store $store)
	{
		$this->store = $store;
		$this->validationToken = $store->getValidationToken()
	}
	
	protected function dirty()
	{
		$this->store->dirty($this)
	}
	
	protected function checkValidationToken()
	{
		if (!$this->validationToken->value)
		{
			die("Tried to acces an invalid Storable. It was probably made invalid by actions" .
		 	    " on its store (like doing a modify, which invalidates all its Storables).")
		}
	}
	
}

?>