<?php

namespace Good\Memory\SQL\ConditionWriter;

use Good\Manners\Condition;
use Good\Manners\Condition\ComplexCondition;
use Good\Manners\Processors\ConditionProcessor;

class ReferenceFragmentWriter implements ConditionProcessor
{
    private $field;

    private $fragment;

    public function tryWritingSimpleFragment($condition, $field)
    {
        $this->field = $field;

        $condition->processCondition($this);

        return $this->success ? $this->fragment : null;
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

        $this->success = true;
    }

    public function processNotEqualToCondition($value)
    {
        if ($value == null)
        {
            $this->fragment = $this->field . ' IS NOT NULL';
        }
        else
        {
            $this->fragment = '(' . $this->field . ' <> ' . \intval($value->getId());
            $this->fragment .= ' OR ' . $this->field . ' IS NULL)';
        }

        $this->success = true;
    }

    public function processGreaterThanCondition($value)
    {
        throw new \Exception("Greater than is not a valid condition for objects");
    }

    public function processGreaterOrEqualCondition($value)
    {
        throw new \Exception("Greater than or equals is not a valid condition for objects");
    }

    public function processLessThanCondition($value)
    {
        throw new \Exception("Less than is not a valid condition for objects");
    }

    public function processLessOrEqualCondition($value)
    {
        throw new \Exception("Less than or equals is not a valid condition for objects");
    }

    public function processComplexCondition(ComplexCondition $condition)
    {
        $this->success = false;
    }

    public function processAndCondition(Condition $condition1, Condition $condition2)
    {
        $this->success = false;
    }

    public function processOrCondition(Condition $condition1, Condition $condition2)
    {
        $this->success = false;
    }
}

?>
