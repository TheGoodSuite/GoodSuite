<?php

//
// set person1's father to be person2
//

include "Model/model.php";

$fetcher = $data->getFetcher("Person");

$person = new Person;

$person->username = $_GET['username1'];
$fetcher->setCondition(new GoodMannersMatches($person);

$person1 = fetcher->fetch()->pop();

$person->username = $_GET['username2'];
$fetcher->setCondition(new GoodMannersMatches($person));

$person2 = fetcher->fetch()->pop();

$person1->father &= $person2;

$data->save();

?>