<?php

//
// Make a new person (eg. register as a user)
//

include "Model/model.php";

$person = new Person;
$person->username = $_POST['username'];
$person->password = $_POST['password'];
$person->firstName = $_POST['firstName'];
$person->lastName = $_POST['lastName'];
$person->father = null;
$person->mother = null;
$person->birthDate = $_POST['birthDate'];
$person->sex = $_POST['sex'] == "m" ? "m" : "v";

$fetcher->setCondition(new GoodMannersMatches($person);

$data->addObject($person);
$data->save();

?>