<?php

namespace Good\Memory;

use Good\Manners\Storable;

class FetchedStorables implements \Good\Manners\FetchedStorables
{
    protected $storage;
    protected $dbresult;
    private $joins;
    private $type;

    private $firstStorable;
    private $lastStorable;

    private $reachedEnd;

    public function __construct($storage, $dbresult, $joins, $type)
    {
        $this->storage = $storage;
        $this->dbresult = $dbresult;
        $this->joins = $joins;
        $this->type = $type;

        $this->firstStorable = new LinkedListElement();
        $this->lastStorable = $this->firstStorable;
        $this->reachedEnd = false;

        $this->queuedRow = null;
    }

    public function getNext()
    {
        $ret = $this->lastStorable;

        if ($this->moveNext())
        {
            return $ret->value;
        }
        else
        {
            return null;
        }
    }

    public function resolveNext(Storable $storable)
    {
        $ret = $this->lastStorable;

        if ($this->moveNextWithResolvableStorable($storable))
        {
            return $ret->value;
        }
        else
        {
            return null;
        }
    }

    public function moveNext()
    {
        return $this->moveNextWithResolvableStorable(null);
    }

    private function moveNextWithResolvableStorable(Storable $storable = null)
    {
        if ($this->reachedEnd)
        {
            return false;
        }

        if ($this->queuedRow != null)
        {
            $row = $this->queuedRow;
        }
        else
        {
            $row = $this->dbresult->fetch();
        }

        if ($row !== null)
        {
            $rows = [$row];
            $keepGoing = true;

            while ($keepGoing)
            {
                $row = $this->dbresult->fetch();

                if ($row === null)
                {
                    $keepGoing = false;
                    $this->reachedEnd = true;
                }
                else if ($row['t0_id'] !== $rows[0]['t0_id'])
                {
                    $keepGoing = false;
                    $this->queuedRow = $row;
                }
                else
                {
                    $rows[] = $row;
                }
            }

            if ($storable == null)
            {
                $this->lastStorable->value = $this->storage->createStorable($rows, $this->joins, $this->type);
            }
            else
            {
                $this->lastStorable->value = $this->storage->resolveStorable($storable, $rows, $this->joins);
            }

            $this->lastStorable->next = new LinkedListElement();
            $this->lastStorable = $this->lastStorable->next;

            return true;
        }
        else
        {
            $this->reachedEnd = true;
            return false;
        }
    }

    public function getIterator()
    {
        if ($this->firstStorable->value == null)
        {
            $this->moveNext();
        }

        return new FetchedStorablesIterator($this, $this->firstStorable);
    }
}

?>
