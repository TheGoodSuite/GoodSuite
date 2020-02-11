<?php

namespace Good\Service;

use Good\Rolemodel\Schema;
use Good\Rolemodel\Schema\Member;
use Good\Rolemodel\Schema\TypeDefinition;

interface Modifier
{
    public function implementingInterfaces();
    public function baseClassBody();
    public function baseClassConstructor();
    public function topOfGetterSwitch(TypeDefinition $typeDefinition);
    public function classBody(TypeDefinition $typeDefinition);
    public function getterBegin(Schema\Member $member);
    public function setterBegin(Schema\Member $member);
    public function setterEnd(Schema\Member $member);
    public function varDefinitionAfter(Schema\Member $member);
    public function extraFiles();
}

?>
