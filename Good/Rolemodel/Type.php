<?php

namespace Good\Rolemodel;

abstract class Type implements Visitable
{
	abstract public function getReferencedTypeIfAny();
}

?>