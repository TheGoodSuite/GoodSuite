<?php

namespace Good\Manners;

use Good\Manners\Comparison\Collection\HasA;
use Good\Manners\Comparison\Collection\HasOnly;
use Good\Manners\Comparison\EqualTo;

class CollectionComparisonsHolder
{
    private $comparisons = [];

    private $collectedType;

    public function __construct(Type $collectedType)
    {
        $this->collectedType = $collectedType;
    }

    public function getComparisons()
    {
        return $this->comparisons;
    }

    public function hasA($comparison = null)
    {
        if ($comparison == null)
        {
            $condition = ${$this->collectedType->getReferencedTypeIfAny()}::condition();

            $this->conditions[] = new HasA($this->collectedType, $condition);

            return $condition;
        }

        if (!($comparison instanceof Comparison))
        {
            $comparison = new EqualTo($comparison);
        }

        $this->comparisons[] = new HasA($comparison);
    }

    public function hasOnly($comparison = null)
    {
        if ($comparison == null)
        {
            $condition = ${$this->collectedType->getReferencedTypeIfAny()}::condition();

            $this->conditions[] = new HasOnly($this->collectedType, $condition);

            return $condition;
        }

        if (!($comparison instanceof Comparison))
        {
            $comparison = new EqualTo($comparison);
        }

        $this->comparisons[] = new HasOnly($comparison);
    }
}

?>
