<?php

namespace Good\Memory\SQL\ConditionWriter;

use Good\Manners\Condition;
use Good\Manners\Condition\ComplexCondition;
use Good\Manners\Processors\ConditionProcessor;

abstract class ScalarFragmentWriter implements ConditionProcessor
{
    protected $field;

    protected $fragment;

    protected abstract function parseScalar($value);

    public function writeFragment($condition, $field)
    {
        $this->field = $field;

        $condition->processCondition($this);

        return $this->fragment;
    }

    public function processEqualToCondition($value)
    {
        if ($value === null)
        {
            $this->fragment = $this->field . ' IS NULL';
        }
        else
        {
            $this->fragment = $this->field . ' = ' . $this->parseScalar($value);
        }
    }

    public function processNotEqualToCondition($value)
    {
        if ($value === null)
        {
            $this->fragment = $this->field . ' IS NOT NULL';
        }
        else
        {
            $this->fragment = $this->field . ' <> ' . $this->parseScalar($value);
        }
    }

    public function processGreaterThanCondition($value)
    {
        $this->fragment = $this->field . ' > ' . $this->parseScalar($value);
    }

    public function processGreaterOrEqualCondition($value)
    {
        $this->fragment = $this->field . ' >= ' . $this->parseScalar($value);
    }

    public function processLessThanCondition($value)
    {
        $this->fragment = $this->field . ' < ' . $this->parseScalar($value);
    }

    public function processLessOrEqualCondition($value)
    {
        $this->fragment = $this->field . ' <= ' . $this->parseScalar($value);
    }

    public function processAndCondition(Condition $condition1, Condition $condition2)
    {
        $fragment = '(' . $this->writeFragment($condition1, $this->field);
        $fragment .= ' AND ';
        $fragment .= $this->writeFragment($condition2, $this->field) . ')';

        $this->fragment = $fragment;
    }

    public function processOrCondition(Condition $condition1, Condition $condition2)
    {
        $fragment = '(' . $this->writeFragment($condition1, $this->field);
        $fragment .= ' OR ';
        $fragment .= $this->writeFragment($condition2, $this->field) . ')';

        $this->fragment = $fragment;
    }

    public function processComplexCondition(ComplexCondition $condition)
    {
        throw new \Exception("Complex condition cannot be applied to non-object fields");
    }
}

?>
