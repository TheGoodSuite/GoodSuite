<?php

//
// Testing GoodRolemodel
//

include '../GoodRolemodel.php';

$rolemodel = new GoodRolemodel();

$res = $rolemodel->createDataModel(array('Person' => 'Person.datatype', 'Address' => 'Address.datatype'));

print_r($res);

?>