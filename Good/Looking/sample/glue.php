<?php
// including the library
include('../../autoload.php');

// starting it up (giving template file name as argument)
$goodLooking = new \Good\Looking\Looking('sampleTemplate.html');

// include the sample application that "gathers" my variables
include('sampleApplication.php');

// Registering the variables with GooLooking
$goodLooking->registerVar('logged_in', $logged_in);
$goodLooking->registerVar('loginpage', $loginpage);
$goodLooking->registerVar('name', $name);
$goodLooking->registerVar('title', $title);
$goodLooking->registerVar('mainText', $mainText);
$goodLooking->registerVar('newspapers', $newspapers);
$goodLooking->registerVar('newspaperLinks', $newspaperLinks);
$goodLooking->registerVar('newspaperNames', $newspaperNames);

// Just to show there's another way to register variables: I will use the other way too
$goodLooking->registerMultipleVars(array('ourFriendsCount' => $ourFriendsCount,
                                           'footer' => $footer,
                                           'insertFooter' => $insertFooter));

// And the one magic word:
$goodLooking->display();
 ?>