<?php

namespace Good\Service;

include_once 'Observable.php';

interface Observer
{
	public function notify(Observable $observable);
}

?>