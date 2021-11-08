<?php

namespace Good\Memory\SQL\ConditionWriter;

use Good\Manners\Condition;
use Good\Manners\Condition\ComplexCondition;
use Good\Manners\Processors\ConditionProcessor;

class ReferenceFragmentWriter implements ConditionProcessor
{
    private $field;

    private $fragment;

    public function writeFragment($condition, $field)
    {
        $this->field = $field;

        $condition->processCondition($this);

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
        $this->writeBracketOrAnd();

        $join = $this->storage->getJoin($this->currentTable, $this->fieldName);

        if ($join == -1)
        {
            $join = $this->storage->createJoin($this->currentTable, $this->fieldName, $type->getReferencedType(), 'id');
        }

        $subWriter = new ConditionWriter($this->storage, $join, $type->getReferencedType());
        $subWriter->writeCondition($this->condition);

        $this->condition .= $subWriter->getCondition();
        $this->appendHaving($subWriter->getHaving());
    }

    public function processAndCondition(Condition $condition1, Condition $condition2)
    {
        $fragment = '(' . $this->writeFragment($condition1);
        $fragment .= ' AND ';
        $fragment .= $this->writeFragment($condition2) . ')';

        $this->fragment = $fragment;
    }

    public function processOrCondition(Condition $condition1, Condition $condition2)
    {
        $fragment = '(' . $this->writeFragment($condition1);
        $fragment .= ' OR ';
        $fragment .= $this->writeFragment($condition2) . ')';

        $this->fragment = $fragment;
    }
}

?>
