<?php

class Blaat
{
    private $bland;
    
    public function blurp()
    {
        $this->bland = 'oso';
        
        eval('?> <?php echo $this->bland;?> <?php;');
    }
}


$b = new Blaat;
$b->blurp();

?>