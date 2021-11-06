<?php

namespace Good\Memory\SQL\ConditionWriter;

use Good\Manners\Condition;
use Good\Manners\ComparisonProcessor;

abstract class ScalarFragmentWriter implements ComparisonProcessor
{
    private $field;

    private $fragment;

    protected abstract function parseScalar($value);

    public function writeFragment($comparison, $field)
    {
        $this->field = $field;

        $comparison->processComparison($this);

        return $this->fragment;
    }

    public function processEqualToComparison($value)
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

    public function processNotEqualToComparison($value)
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

    public function processGreaterThanComparison($value)
    {
        $this->fragment = $this->field . ' > ' . $this->parseScalar($value);
    }

    public function processGreaterOrEqualComparison($value)
    {
        $this->fragment = $this->field . ' >= ' . $this->parseScalar($value);
    }

    public function processLessThanComparison($value)
    {
        $this->fragment = $this->field . ' < ' . $this->parseScalar($value);
    }

    public function processLessOrEqualComparison($value)
    {
        $this->fragment = $this->field . ' <= ' . $this->parseScalar($value);
    }

    public function processAndComparison(Condition $comparison1, Condition $comparison2)
    {
        $fragment = '(' . $this->writeFragment($comparison1, $this->field);
        $fragment .= ' AND ';
        $fragment .= $this->writeFragment($comparison2, $this->field) . ')';

        $this->fragment = $fragment;
    }

    public function processOrComparison(Condition $comparison1, Condition $comparison2)
    {
        $fragment = '(' . $this->writeFragment($comparison1, $this->field);
        $fragment .= ' OR ';
        $fragment .= $this->writeFragment($comparison2, $this->field) . ')';

        $this->fragment = $fragment;
    }
}

?>
