<?php

include_once 'Observable.php';

interface GoodServiceObserver
{
	public function notify(GoodServiceObservable $observable);
}

?>