<?php

namespace Good\Service;

interface Observable
{
	public function register(Observer $observer);
	public function unregister(Observer $observer);
}

?>