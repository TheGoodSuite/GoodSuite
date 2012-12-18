<?php
 
 include('preread.php');
 
 $myTemplate = new Template('sampleTemplate.html');
 
 include('sampleApplication.php');
 
 $myTemplate->registerVar('logged_in', $logged_in);
 $myTemplate->registerVar('loginpage', $loginpage);
 $myTemplate->registerVar('name', $name);
 $myTemplate->registerVar('title', $title);
 $myTemplate->registerVar('mainText', $mainText);
 $myTemplate->registerVar('newspapers', $newspapers);
 $myTemplate->registerVar('newspaperLinks', $newspaperLinks);
 $myTemplate->registerVar('newspaperNames', $newspaperNames);
 $myTemplate->registerVar('ourFriendsCount', $ourFriendsCount);
 $myTemplate->registerVar('footer', $footer);
 $myTemplate->registerVar('insertFooter', $insertFooter);
 
 $myTemplate->output();
 ?>