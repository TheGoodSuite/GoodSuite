<?php

//
// Find all people that have a grandmother in the database
//

include 'Model/model.php';

$fetcher = $data->getFetcher("Person");

$fetcher->mother->resolve();
$fetcher->mother->mother->resolve();

$results = $fetcher->fetch();

// username => username grandmas
$grandmas = array();

foreach($results as $person)
{
    if ($person->mother != null &&
         $person->mother->mother != null)
    {
        $grandmas[$person->username] = $person->mother->mother->username;
    }
}

?>