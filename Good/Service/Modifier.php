<?php

// TODO: fix include (currently dependent on location of current script)
include_once '../../Rolemodel/Visitor.php';

interface GoodServiceModifier extends GoodRolemodelVisitor
{
	public function baseClassTopOfFile();
	public function implementingInterfaces();
	public function baseClassBody();
	public function baseClassConstructor();
	public function getterBegin();
	public function setterBegin();
	public function setterEnd();
	public function nullGetterBegin();
	public function nullSetterBegin();
	public function nullSetterEnd();
	public function varDefinitionBefore();
	public function varDefinitionAfter();
	public function topOfFile();
	public function bottomOfFile();
	public function extraFiles();
}

?>