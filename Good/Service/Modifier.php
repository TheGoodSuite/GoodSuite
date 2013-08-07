<?php

namespace Good\Service;

interface Modifier extends \Good\Rolemodel\SchemaVisitor
{
    public function baseClassTopOfFile();
    public function implementingInterfaces();
    public function baseClassBody();
    public function baseClassConstructor();
    public function getterBegin();
    public function setterBegin();
    public function setterEnd();
    public function varDefinitionBefore();
    public function varDefinitionAfter();
    public function topOfFile();
    public function bottomOfFile();
    public function extraFiles();
}

?>