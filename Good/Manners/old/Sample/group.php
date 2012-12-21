<?php

//
// Find username for each person in each group
//

include "Model/model.php";

$fetcher = $data->getFetcher("Group");

$fetcher->members->resolve();

$result = $fetcher->fetch();

// groupname => array of usernames
$users = array();

for ($group in $result)
{
    users[$group->groupName] = array;
    
    for ($user in $group->members)
    {
        users[$group->groupName][] = $user->username;
    }
}

?>