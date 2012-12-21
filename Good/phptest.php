<?php

class BaseArgument
{
}

class ChildArgument extends BaseArgument
{
}
function myErrorHandler($errno, $errstr, $errfile, $errline) {
  if ( E_RECOVERABLE_ERROR===$errno ) {
    echo "'caught' catchable fatal error <br />";
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    // return true;
  }
  return false;
}

class Base
{
    public function foo(BaseArgument $arg)
    {
        set_error_handler('myErrorHandler');
        try
        {
            $this->realFoo($arg);
        }
        catch (Exception  $e)
        {
            parent::realFoo($arg);
        }
        restore_error_handler();
    }
    
    protected function realFoo(BaseArgument $arg)
    {
        echo "Base function called <br />";
    }
}

class Child extends Base
{
    public function foo(BaseArgument $arg)
    {
        set_error_handler('myErrorHandler');
        try
        {
            $this->realFoo($arg);
        }
        catch (Exception  $e)
        {
            parent::foo($arg);
        }
        restore_error_handler();
    }
    
    protected function realFoo(ChildArgument $arg)
    {
        echo "Child function called <br />";
    }
}

$base = new Base;
$child = new Child;

$baseArg = new BaseArgument;
$childArg = new ChildArgument;

$base->foo($baseArg);

$child->foo($childArg);
$child->foo($baseArg);  

?>