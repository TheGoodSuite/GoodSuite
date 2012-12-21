<?php

//
// Testing GoodRolemodel
//

include_once '../../Rolemodel/GoodRolemodel.php';
include_once '../ModifierObservable.php';
include_once '../../Manners/ModifierStorable.php';
include_once '../GoodService.php';
include_once '../Observer.php';

$rolemodel = new GoodRolemodel();

$model = $rolemodel->createDataModel(array('Person' => 'Person.datatype', 'Address' => 'Address.datatype'));

$modifiers = array(new GoodServiceModifierObservable(),
				   new GoodMannersModifierStorable());

$service = new GoodService();

$service->compile($modifiers, $model, 'compiled/');

?>