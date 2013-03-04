<?php

include 'glue.php'

?>

<script type='text/javascript'>
  
  var compileTime = <?php echo isset($compileTime) ? "'" . $compileTime . "'" : 'null' ?>;
  var interpretingTime = '<?php echo $interpretTime ?>';
  
  var output = '<h3>Stats</h3><p>';
  
  if (compileTime != null)
  {
      output += 'Compile time: ' + compileTime + ' seconds<br />';
  }
  
  output += 'Interpreting time: ' + interpretingTime + ' seconds<br />';
  
  top.document.getElementById('menuframe').contentDocument.getElementById('stats').innerHTML = output;
</script>
