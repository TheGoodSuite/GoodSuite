<?php

namespace Good\Rolemodel;

//
// We are working on a structure that doesn't adhere too closely to the
// composite pattern.
// Nevertheless, we have a tree structure which we want to decouple from 
// the functionality from the tree structure itself. The Visitor pattern
// does just that.
// 

interface Visitor
{
	public function visitSchema($dataModel);
	public function visitSchemaEnd();
	public function visitDataType($dataType);
	public function visitReferenceMember($type);
	public function visitTextMember($type);
	public function visitIntMember($type);
	public function visitFloatMember($type);
	public function visitDatetimeMember($type);
}

?>