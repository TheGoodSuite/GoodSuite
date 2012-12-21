<?php

include "Model/Person.php";
include "Model/Group.php";

$data = new GoodMannersSQLStorage(array("Group"), new DbMySQL());

// the following line woud do the exact same, considering the
// Person class is included implicitly through the fact 
// Group references it:
//new GoodMannersSQLStorage(array("Group", "Person"), new DbMySQL());

?>