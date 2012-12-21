<?php

include_once 'Observer.php';

interface GoodServiceObservable
{
	public function register(GoodServiceObserver $observer);
	public function unregister(GoodServiceObserver $observer);
}

?>