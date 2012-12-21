<?php

//
// Log a user in
//

include "Model/model.php";

$fetcher = $data->getFetcher("Person");

$person = new Person;
$person->username = $_POST['username'];
$person->password = $_POST['password'];

$fetcher->setCondition(GoodMannersMatches($person);

$result = $fetcher->fetch();

if ($result->getCount() == 1)
{
    $_SESSION['user'] = $result->pop();
}
else
{
    $_SESSION['user'] = null;
}

?>