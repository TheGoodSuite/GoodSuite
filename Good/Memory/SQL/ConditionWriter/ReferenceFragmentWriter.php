<?php

namespace Good\Memory\SQL\ConditionWriter;

use Good\Manners\Condition;
use Good\Manners\EqualityComparisonProcessor;

class ReferenceFragmentWriter implements EqualityComparisonProcessor
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
