<?php

require_once 'ReferenceValue.php';
require_once 'TextValue.php';
require_once 'IntValue.php';
require_once 'FloatValue.php';

interface GoodMannersValueVisitor
{
	public function visitReferenceValue(GoodMannersReferenceValue $value);
	public function visitTextValue(GoodMannersTextValue $value);
	public function visitIntValue(GoodMannersIntValue $value);
	public function visitFloatValue(GoodMannerwsFloatValue $value);
}

?>