<?php

namespace Good\Manners;

use Good\Manners\Storable;

//
// Where Storable describes the functional requirements that must be met to be stored
// in a Storage, this interface adds the other public API functions that StorableModifier
// adds to your generated classes
//


interface GeneratedStorable extends Storable
{
    public function setFromArray(array $values);
    public static function resolver();
}


?>