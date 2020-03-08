<?php

namespace Good\Memory\SQL\ConditionWriter;

use Good\Manners\Comparison;
use Good\Manners\ComparisonProcessor;

abstract class ScalarFragmentWriter implements ComparisonProcessor
{
    private $field;

    private $fragment;

    protected abstract function parseScalar($value);

    public function __construct($field)
    {
        $this->field = $field;
    }

    public function writeFragment($comparison)
    {
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
            $this->fragment = $this->field . ' = ' . $this->parseScalar($value);
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

    public function processAndComparison(Comparison $comparison1, Comparison $comparison2)
    {
        $fragment = '(' . $this->writeFragment($comparison1);
        $fragment .= ' AND ';
        $fragment .= $this->writeFragment($comparison2) . ')';

        $this->fragment = $fragment;
    }

    public function processOrComparison(Comparison $comparison1, Comparison $comparison2)
    {
        $fragment = '(' . $this->writeFragment($comparison1);
        $fragment .= ' OR ';
        $fragment .= $this->writeFragment($comparison2) . ')';

        $this->fragment = $fragment;
    }
}

?>
