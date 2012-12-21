<?php

//
// We are working on a structure that doesn't adhere too closely to the
// composite pattern.
// Nevertheless, we have a tree structure which we want to decouple from 
// the functionality from the tree structure itself. The Visitor pattern
// does just that.
// 

include_once 'Visitor.php';

interface GoodRolemodelVisitable
{
	public function accept(GoodRolemodelVisitor $visitor);
}

?>