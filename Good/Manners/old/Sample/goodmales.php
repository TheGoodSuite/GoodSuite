<?php

//
// Find all males in groups with an avarage grade of 7.5 or higher
//

$groupFetcher = $data->getFetcher("Group");

$group = new Group;
$group->avarageGrade = 7.5;

$groupFetcher->setCondition(new GoodMannersEqualsOrHigher($group));

$fetcher = $data->getFetcher("Person");

$male = new Person;
$male->sex = 'm';

$fetcher->setCondition(new GoodMannersAnd(new GoodMannerMatches($male),
                                          new GoodMannersIn($groupFetcher, "members"));

$results = $fetcher->fetch();

?>