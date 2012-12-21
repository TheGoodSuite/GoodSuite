<?php

require_once 'Store.php';
require_once 'ValidationToken.php';

//
// Note that while a class needs to adhere to this Storable interface in order
// to be stored in a Store, but it also needs to implement some less obvious
// behaviour (like telling the store whenever it gets dirty).
//


interface GoodMannersStorable
{
	public function delete();
	public function isDeleted();
	public function setNew($value);
	public function isNew();
	public function setStore(GoodMannersStore &$store);
	public function setValidationToken(GoodMannersValidationToken &$token)
	public function isBlank();
	public function report();
	public function getId();
	public function isDirty();
	
	public static function getBlank();
}


?>