<?php

class Group
{
    public $groupName = new GoodMannersString;
    public $groupLevel = new GoodMannersInt;
    public $groupAvarageGrade = new GoodMannersFloat;
    
    public $members = new GoodMannersArray(new GoodMannersClass("Person");
}

?>