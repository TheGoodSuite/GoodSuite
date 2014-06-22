<?php

namespace Good\Looking;

/*
 * There are some aspects of this interface that go beyond what you can express in
 * php code. These are:
 *
 * - Reflection may be used on a class implementing this interfface to find out in
 *   which file it was defined.
 * - That file may be included at another time (i.e. another process). At that point,
 *   all the functionality defined by the interface (including the constructor)
 *   should be fully working. This means that you can't rely on other code having
 *   been executed earlier, not even your autoloader.
 */
interface FunctionHandler
{
    public function __construct();
    public function getHandledfunctions();
    public function handleFunction($functionName, FunctionHelper $helper, array $arguments);
}

?>