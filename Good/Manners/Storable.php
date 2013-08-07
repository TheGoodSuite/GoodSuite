<?php

namespace Good\Manners;

//
// Note that while a class needs to adhere to this Storable interface in order
// to be stored in a Storagee, but it also needs to implement some less obvious
// behaviour (like telling the storage whenever it gets dirty).
//


interface Storable
{
    public function delete();
    public function isDeleted();
    public function setNew($value);
    public function isNew();
    public function setStorage(Storage $storage);
    public function setValidationToken(ValidationToken $token);
    public function getId();
    public function setId($value);
    public function isDirty();
    public function clean();
    public function getType();
    public function acceptStorableVisitor(StorableVisitor $visitor);
    public function setFromArray(array $values);
    
    public static function resolver();
}


?>