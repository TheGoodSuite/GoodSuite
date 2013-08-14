<?php

namespace Good\Memory\SQL;

use Good\Memory\SQLStorage;
use Good\Manners\Storable;
use Good\Manners\StorableVisitor;
use Good\Manners\Condition;
use Good\Manners\ConditionProcessor;

class ConditionWriter implements StorableVisitor,
                                 ConditionProcessor
{
    private $storage;
    private $comparison;
    private $condition;
    private $first;
    
    private $currentTable;
    
    public function __construct(SQLStorage $storage, $currentTable)
    {    
        $this->storage = $storage;
        $this->currentTable = $currentTable;
    }
    
    public function getCondition()
    {
        return $this->condition;
    }
    
    public function writeCondition(Condition $condition)
    {
        $condition->process($this);
    }
    
    public function writeComparisonCondition(Storable $to, $comparison)
    {
        $this->comparison = $comparison;
        $this->first = true;
        $this->condition = '';
        
        $to->acceptStorableVisitor($this);
        
        if ($this->first)
        {
            if ($to->getId() != -1)
            {
                $this->condition .= 't' . $this->currentTable . '.id' .
                                        ' ' . $this->comparison . ' ' . \intval($to->getId());
            }
            else
            {
                $this->condition = '1 = 1';
            }
        }
    }
    
    public function processEqualToCondition(Storable $to)
    {
        $this->writeComparisonCondition($to, '=');
    }
    public function processNotEqualToCondition(Storable $to)
    {
        $this->writeComparisonCondition($to, '<>');
    }
    public function processGreaterThanCondition(Storable $to)
    {
        $this->writeComparisonCondition($to, '>');
    }
    public function processGreaterOrEqualCondition(Storable $to)
    {
        $this->writeComparisonCondition($to, '>=');
    }
    public function processLessThanCondition(Storable $to)
    {
        $this->writeComparisonCondition($to, '<');
    }
    public function processLessOrEqualCondition(Storable $to)
    {
        $this->writeComparisonCondition($to, '<=');
    }
    
    public function processAndCondition(Condition $condition1, Condition $condition2)
    {
        $this->writeCondition($condition1);
        $sqlCondition1 = $this->getCondition();
        
        $this->writeCondition($condition2);
        $sqlCondition2 = $this->getCondition();
        
        $this->condition = '(' . $sqlCondition1 . ' AND ' . $sqlCondition2 . ')';
    }
    public function processOrCondition(Condition $condition1, Condition $condition2)
    {
        $this->writeCondition($condition1);
        $sqlCondition1 = $this->getCondition();
        
        $this->writeCondition($condition2);
        $sqlCondition2 = $this->getCondition();
        
        $this->condition = '(' . $sqlCondition1 . ' OR ' . $sqlCondition2 . ')';
    }
    
    public function visitReferenceProperty($name, $datatypeName, $dirty, 
                                                            Storable $value = null)
    {
        if ($dirty)
        {
            $this->writeBracketOrAnd();
            
            if($value === null)
            {
                if ($this->comparison == '=')
                {
                    $this->condition .= 't' . $this->currentTable . '.' . $this->storage->fieldNamify($name) . 
                                                ' IS NULL';
                }
                else // if ($this->comparison == '<>')
                {
                    $this->condition .= 't' . $this->currentTable . '.' . $this->storage->fieldNamify($name) . 
                                                ' IS NOT NULL';
                }
                
                // todo: error out if not EqualTo or NotEqualTo
            }
            else if (!$value->isNew())
            {
                $this->condition .= 't' . $this->currentTable . '.' . $this->storage->fieldNamify($name) . 
                                            $this->comparison . ' ' . \intval($value->getId());
                
                // todo: error out if not EqualTo or NotEqualTo
            }
            else
            {
                $join = $this->storage->getJoin($this->currentTable, $name);
                
                if ($join == -1)
                {
                    $join = $this->storage->createJoin($this->currentTable, $name, $datatypeName);
                }
                
                $subWriter = new ConditionWriter($this->storage, $join);
                $subWriter->writeComparisonCondition($value, $this->comparison);
                
                $this->condition .= $subWriter->getCondition();
            }
        }
    }
    
    public function visitTextProperty($name, $dirty, $value)
    {
        if($dirty)
        {
            $this->writeBracketOrAnd();
        
            $this->condition .= 't' . $this->currentTable . '.' . $this->storage->fieldNamify($name) . ' ';
            if ($value === null)
            {
                if ($this->comparison == '=')
                {
                    $this->condition .= ' IS NULL';
                }
                else // if ($this->comparison == '<>')
                {
                    $this->condition .= ' IS NOT NULL';
                }
                
                // todo: error out if not EqualTo or NotEqualTo
            }
            else
            {
                $this->condition .= $this->comparison .' ' . $this->storage->parseText($value);
            }
            
        }
    }
    public function visitIntProperty($name, $dirty, $value)
    {
        if($dirty)
        {
            $this->writeBracketOrAnd();
        
            $this->condition .= 't' . $this->currentTable . '.' . $this->storage->fieldNamify($name) . ' ';
            if ($value === null)
            {
                if ($this->comparison == '=')
                {
                    $this->condition .= ' IS NULL';
                }
                else // if ($this->comparison == '<>')
                {
                    $this->condition .= ' IS NOT NULL';
                }
                
                // todo: error out if not EqualTo or NotEqualTo
            }
            else
            {
                $this->condition .= $this->comparison .' ' . $this->storage->parseInt($value);
            }
        }
    }
    public function visitFloatProperty($name, $dirty, $value)
    {
        if($dirty)
        {
            $this->writeBracketOrAnd();
        
            $this->condition .= 't' . $this->currentTable . '.' . $this->storage->fieldNamify($name) . ' ';
            if ($value === null)
            {
                if ($this->comparison == '=')
                {
                    $this->condition .= ' IS NULL';
                }
                else // if ($this->comparison == '<>')
                {
                    $this->condition .= ' IS NOT NULL';
                }
                
                // todo: error out if not EqualTo or NotEqualTo
            }
            else
            {
                $this->condition .= $this->comparison .' ' . $this->storage->parseFloat($value);
            }
        }
    }
    public function visitDatetimeProperty($name, $dirty, $value)
    {
        if($dirty)
        {
            $this->writeBracketOrAnd();
        
            $this->condition .= 't' . $this->currentTable . '.' . $this->storage->fieldNamify($name) . ' ';
            if ($value === null)
            {
                if ($this->comparison == '=')
                {
                    $this->condition .= ' IS NULL';
                }
                else // if ($this->comparison == '<>')
                {
                    $this->condition .= ' IS NOT NULL';
                }
                
                // todo: error out if not equality or NotEqualTo
            }
            else
            {
                $this->condition .= $this->comparison .' ' . $this->storage->parseDatetime($value);
            }
        }
    }
    
    private function writeBracketOrAnd()
    {
        if ($this->first)
        {
            // removed brackets change name of function?
            //$this->condition = '(';
            $this->first = false;
        }
        else
        {
            $this->condition .= ' AND ';
        }
    }
}

?>