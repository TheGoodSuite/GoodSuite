<?php
$input = file_get_contents("sampleTemplate.html");

//for testing I really want to know how long this function takes to be executed
$executionTime = microtime(true);

$input = implode("<EXPLOSION MARK(random id)*><:-", explode("<:-", $input));
$input = implode("<EXPLOSION MARK(random id)*><:-", explode("<:-", $input));
$input = implode("<EXPLOSION MARK(random id)*><:-", explode("<:-", $input));
$input = implode("<EXPLOSION MARK(random id)*><:-", explode("<:-", $input));

$input_array = explode("<EXPLOSION MARK(random id)*>", $input);

//for testing I wanna know how long this function outputs how long it took
echo "<div style='display: none;'>";
print_r((microtime(true)-$executionTime));
echo " seconds</div>";

?>