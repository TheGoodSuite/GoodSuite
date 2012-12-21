<?php

ini_set('allow_call_time_pass_reference', 'Off');

class Person{
    public $Name;
}
function Something(Person $Person){
    $Person->Name = 'Jimbob';
}
$A = new Person();
$A->Name = 'James';
Something($A);
echo $A->Name; //Jimbob  

?>