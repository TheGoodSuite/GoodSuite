<?php

namespace Good\Service;

include_once 'Observer.php';

interface Observable
{
	public function register(Observer $observer);
	public function unregister(Observer $observer);
}

?>