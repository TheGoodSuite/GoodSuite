<?php

function entDeep(&$array)
{
    if (is_array($array))
    {
        foreach ($array as &$elem)
        {
            entDeep($elem);
        }
    }
    else
    {
        $array = htmlentities($array);
    }
    
    return $array;
}

?>