<?php

namespace Good\Rolemodel;

include_once 'Visitable.php';

abstract class Type implements Visitable
{
	abstract public function getReferencedTypeIfAny();
}

?>