<?php

namespace Good\Memory\SQL;

class OrderLayer
{
    public $rootTableNumber;
    public $orderClauses;
    public $childLayers;

    public function __construct($rootTableNumber, $orderClauses = [])
    {
        $this->rootTableNumber = $rootTableNumber;
        $this->orderClauses = $orderClauses;
        $this->childLayers = [];
    }
}

?>
