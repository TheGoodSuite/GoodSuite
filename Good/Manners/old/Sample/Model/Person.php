<?php

class Person
{
    public $username = new GoodMannersString;
    public $password = new GoodMannersHash;
    
    public $firstName = new GoodMannersString;
    public $lastName = new GoodMannersString;
    
    public $father = new GoodMannersClass("Person");
    public $mother = new GoodMannersClass("Person");
    
    public $birthDate = new GoodMannersDate;
    
    public $sex = new GoodMannersEnum(array("m", "v"));
}

?>