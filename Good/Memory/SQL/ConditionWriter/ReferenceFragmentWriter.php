<?php

namespace Good\Memory\SQL\ConditionWriter;

use Good\Manners\Condition;
use Good\Manners\ComparisonProcessor;

class ReferenceFragmentWriter implements ComparisonProcessor
{
    private $field;

    private $fragment;

    public function writeFragment($comparison, $field)
    {
        $this->field = $field;

        $comparison->processComparison($this);

        return $this->fragment;
    }

    public function processEqualToComparison($value)
    {
        if ($value == null)
        {
            $this->fragment = $this->field . ' IS NULL';
        }
        else
        {
            $this->fragment = $this->field . ' = ' . \intval($value->getId());
        }
    }

    public function processNotEqualToComparison($value)
    {
        if ($value == null)
        {
            $this->fragment = $this->field . ' IS NOT NULL';
        }
        else
        {
            $this->fragment = $this->field . ' <> ' . \intval($value->getId());
        }
    }

    public function processGreaterThanComparison($value)
    {
        throw new \Exception("Greater than is not a valid comparison for objects");
    }

    public function processGreaterOrEqualComparison($value)
    {
        throw new \Exception("Greater than or equals is not a valid comparison for objects");
    }

    public function processLessThanComparison($value)
    {
        throw new \Exception("Less than is not a valid comparison for objects");
    }

    public function processLessOrEqualComparison($value)
    {
        throw new \Exception("Less than or equals is not a valid comparison for objects");
    }

    public function processAndComparison(Condition $comparison1, Condition $comparison2)
    {
        $fragment = '(' . $this->writeFragment($comparison1);
        $fragment .= ' AND ';
        $fragment .= $this->writeFragment($comparison2) . ')';

        $this->fragment = $fragment;
    }

    public function processOrComparison(Condition $comparison1, Condition $comparison2)
    {
        $fragment = '(' . $this->writeFragment($comparison1);
        $fragment .= ' OR ';
        $fragment .= $this->writeFragment($comparison2) . ')';

        $this->fragment = $fragment;
    }
}

?>
