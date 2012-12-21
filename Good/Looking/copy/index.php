<?php
 
 include('GoodLooking.php');
 
 $goodLooking = new GoodLooking('sampleTemplate.html');
 
 include('sampleApplication.php');
 
 $goodLooking->registerVar('logged_in', $logged_in);
 $goodLooking->registerVar('loginpage', $loginpage);
 $goodLooking->registerVar('name', $name);
 $goodLooking->registerVar('title', $title);
 $goodLooking->registerVar('mainText', $mainText);
 $goodLooking->registerVar('newspapers', $newspapers);
 $goodLooking->registerVar('newspaperLinks', $newspaperLinks);
 $goodLooking->registerVar('newspaperNames', $newspaperNames);
 $goodLooking->registerMultipleVars(array('ourFriendsCount' => $ourFriendsCount,
                                           'footer' => $footer,
                                           'insertFooter' => $insertFooter));

 $goodLooking->display();
 ?>