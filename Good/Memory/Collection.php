<?php

namespace Good\Memory;

class Collection
{
    protected $store;
    protected $dbresult;
    private $joins;
    private $type;

    public function __construct($store, $dbresult, $joins, $type)
    {
        $this->store = $store;
        $this->dbresult = $dbresult;
        $this->joins = $joins;
        $this->type = $type;
    }
    
    public function getNext()
    {
        if ($row = $this->dbresult->fetch())
        {
            return $this->store->createStorable($row, $this->joins, $this->type);
        }
        else
        {
            return null;
        }
    }
}

?>