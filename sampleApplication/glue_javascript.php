<?php
// including the library
include('../GoodLooking.php');

// starting it up (giving template file name as argument)
$goodLooking = new GoodLooking('sampleTemplate.html');

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


// and now some added code that's not in the version we show (or in any real version)
// which outputs the compile and interpreting times
?><!-- from here, magically insterted especially for the demo, to display the running times -->
<script type='text/javascript'>
window.onload = function ()
{
  var html = document.documentElement.innerHTML;
  
  var compileTime = html.match(/<!-- Compiling took:([0-9.E-]+).* seconds -->/);
  var interpretingTime = html.match(/<!-- Interpreting took:([0-9.E-]+) seconds -->/);
  
  var output = '<h3>Stats</h3><p>';
  
  if (compileTime != null)
  {
      output += 'Compile time: ' + compileTime[1] + '<br />';
  }
  
  output += 'Interpreting time: ' + interpretingTime[1] + '<br />';
  
  parent.document.getElementById('menuframe').contentDocument.getElementById('stats').innerHTML = output;
}
</script>
<!-- end of magically insterted code --></body>
</html>