<?php

interface GoodRolemodelVisitor
{
	public function visitDataModel($dataModel);
	public function visitDataType($dataType);
	public function visitDataMember($dataMember);
	public function visitTypeReference($type);
	public function visitTypePrimitiveText($type);
	public function visitTypePrimitiveInt($type);
	public function visitTypePrimitiveFloat($type);
	public function visitEnd();
}

?>