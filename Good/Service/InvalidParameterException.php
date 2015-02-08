<?php

namespace Good\Service;


// Just a quick trick to make it possible to catch these exception for testing
// A better implementation is of course required when the true error handling is introduced
class InvalidParameterException extends \Exception
{
}

?>