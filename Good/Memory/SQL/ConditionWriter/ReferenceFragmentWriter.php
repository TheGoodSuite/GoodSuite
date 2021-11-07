<?php

namespace Good\Memory\SQL\ConditionWriter;

use Good\Manners\Condition;
use Good\Manners\Condition\ComplexCondition;
use Good\Manners\Processors\ConditionProcessor;

class ReferenceFragmentWriter implements ConditionProcessor
{
    private $field;

    private $fragment;

    public function writeFragment($comparison, $field)
    {
        $this->field = $field;

        $comparison->processCondition($this);

        return $this->fragment;
    }

    public function processEqualToCondition($value)
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

    public function processNotEqualToCondition($value)
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

    public function processGreaterThanCondition($value)
    {
        throw new \Exception("Greater than is not a valid comparison for objects");
    }

    public function processGreaterOrEqualCondition($value)
    {
        throw new \Exception("Greater than or equals is not a valid comparison for objects");
    }

    public function processLessThanCondition($value)
    {
        throw new \Exception("Less than is not a valid comparison for objects");
    }

    public function processLessOrEqualCondition($value)
    {
        throw new \Exception("Less than or equals is not a valid comparison for objects");
    }

    public function processComplexCondition(ComplexCondition $condition)
    {
        throw new \Exception("Using complex conditions for a reference comparison should follow a different code path than this...");
    }

    public function processAndCondition(Condition $comparison1, Condition $comparison2)
    {
        $fragment = '(' . $this->writeFragment($comparison1);
        $fragment .= ' AND ';
        $fragment .= $this->writeFragment($comparison2) . ')';

        $this->fragment = $fragment;
    }

    public function processOrCondition(Condition $comparison1, Condition $comparison2)
    {
        $fragment = '(' . $this->writeFragment($comparison1);
        $fragment .= ' OR ';
        $fragment .= $this->writeFragment($comparison2) . ')';

        $this->fragment = $fragment;
    }
}

?>
