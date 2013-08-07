<?php

private $installed;

if (!file_exists('installed'))
{
    require 'install.php';
}

if (!file_exists('../installed'))
{
    require 'installGood.php';
}

$good = new Good();
$friend = $good->module('Friend');



?>