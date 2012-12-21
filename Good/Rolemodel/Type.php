<?php

include_once 'Visitable.php';

abstract class GoodRolemodelType implements GoodRolemodelVisitable
{
	abstract public function getReferencedTypeIfAny();
}

?>