<?php

//
// Have the person with a certain username become of a certain sex
//

include 'Model/model.php';

$fetcher = $data->getFetcher();

$person = new Person;
$person->username = $_GET['username'];

$fetcher->setCondition(new GoodMannersMatches($person));

$results = $fetcher->fetch();

if ($results->getCount() == 1)
{
    $user = $results->pop();
    
    $user->sex = $_GET['sex_to'] == 'm' ? 'm' : 'v';
    
    $data->save();
}

?>