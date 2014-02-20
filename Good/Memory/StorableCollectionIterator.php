<?php

namespace Good\Memory;

class StorableCollectionIterator implements \Iterator
{
    private $collection;
    private $firstElement;
    private $currentElement;
    
    public function __construct(StorableCollection $collection, LinkedListElement $firstElement)
    {
        $this->collection = $collection;
        $this->firstElement = $firstElement;
        $this->currentElement = $firstElement;
    }
    
    public function current()
    {
        if ($this->currentElement->value == null)
        {
            $this->collection->moveNext();
        }
        
        return $this->currentElement->value;
    }
    
    public function key()
    {
        return null;
    }
    
    public function next()
    {
        if ($this->currentElement->next == null)
        {
            $this->collection->moveNext();
        }
        
        $this->currentElement = $this->currentElement->next;
    }
    
    public function rewind()
    {
        $this->currentElement = $this->firstElement;
    }
    
    public function valid()
    {
        if ($this->currentElement->value == null)
        {
            return $this->collection->moveNext();
        }
        else
        {
            return true;
        }
    }
}

?>
