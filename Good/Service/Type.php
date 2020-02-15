<?php

namespace Good\Service;

interface Type extends \Good\Rolemodel\Schema\Type
{
    public function checkValue($value);
}

?>
