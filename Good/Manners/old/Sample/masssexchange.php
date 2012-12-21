<?php

//
// All people of 40 or over become women
//

include 'Model/model.php'

$changer = $data->getChanger();

$female = new Person;
$female->sex = 'v';

$old = new Person;
$time = new DateTime();
$time->sub(new DateInterval("P40Y"));
$old->birthDate = $date;

// This is not necessary, but it avoids changing records to what they
// already are
$male = new Person;
$male->sex = 'm';

$changer->changeTo($female);
$changer->setCondition(GoodMannersAnd(GoodMannersMatches($male),
                                      GoodMannersEqualsOrLess($old));

$changer->change();

?>