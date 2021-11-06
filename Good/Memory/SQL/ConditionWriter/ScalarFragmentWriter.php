<?php

namespace Good\Memory\SQL\ConditionWriter;

use Good\Manners\Condition;
use Good\Manners\Condition\ComplexCondition;
use Good\Manners\ConditionProcessor;

abstract class ScalarFragmentWriter implements ConditionProcessor
{
    private $field;

    private $fragment;

    protected abstract function parseScalar($value);

    public function writeFragment($comparison, $field)
    {
        $this->field = $field;

        $comparison->processCondition($this);

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

    public function processAndCondition(Condition $comparison1, Condition $comparison2)
    {
        $fragment = '(' . $this->writeFragment($comparison1, $this->field);
        $fragment .= ' AND ';
        $fragment .= $this->writeFragment($comparison2, $this->field) . ')';

        $this->fragment = $fragment;
    }

    public function processOrCondition(Condition $comparison1, Condition $comparison2)
    {
        $fragment = '(' . $this->writeFragment($comparison1, $this->field);
        $fragment .= ' OR ';
        $fragment .= $this->writeFragment($comparison2, $this->field) . ')';

        $this->fragment = $fragment;
    }

    public function processComplexCondition(ComplexCondition $condition)
    {
        throw new \Exception("Complex condition cannot be applied to non-object fields");
    }
}

?>
