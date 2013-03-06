<?php

namespace Good\Service;

interface Observer
{
	public function notify(Observable $observable);
}

?>