<?php

namespace Good\Memory;

class Collection implements \Good\Manners\Collection
{
    protected $storage;
    protected $dbresult;
    private $joins;
    private $type;

    public function __construct($storage, $dbresult, $joins, $type)
    {
        $this->storage = $storage;
        $this->dbresult = $dbresult;
        $this->joins = $joins;
        $this->type = $type;
    }
    
    public function getNext()
    {
        if ($row = $this->dbresult->fetch())
        {
            return $this->storage->createStorable($row, $this->joins, $this->type);
        }
        else
        {
            return null;
        }
    }
}

?>