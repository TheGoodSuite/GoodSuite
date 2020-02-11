<?php

namespace Good\Service;

use Good\Rolemodel\Schema;
use Good\Rolemodel\Schema\Member;
use Good\Rolemodel\Schema\TypeDefinition;
use Good\Rolemodel\Schema\Type\DateTimeType;
use Good\Rolemodel\Schema\Type\FloatType;
use Good\Rolemodel\Schema\Type\IntType;
use Good\Rolemodel\Schema\Type\ReferenceType;
use Good\Rolemodel\Schema\Type\TextType;

interface Modifier
{
    public function baseClassTopOfFile();
    public function implementingInterfaces();
    public function baseClassBody();
    public function baseClassConstructor();
    public function getterBegin();
    public function setterBegin();
    public function setterEnd();
    public function topOfGetterSwitch();
    public function varDefinitionBefore();
    public function varDefinitionAfter();
    public function topOfFile();
    public function bottomOfFile();
    public function extraFiles();

    public function visitSchema(Schema $schema);
    public function visitSchemaEnd();
    public function visitTypeDefinition(TypeDefinition $typeDefinition);
    public function visitDatetimeMember(Member $member, DateTimeType $type);
    public function visitFloatMember(Member $member, FloatType $type);
    public function visitIntMember(Member $member, IntType $type);
    public function visitReferenceMember(Member $member, ReferenceType $type);
    public function visitTextMember(Member $member, TextType $type);
}

?>
