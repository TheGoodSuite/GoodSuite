<?php


abstract class GoodMannersQueryAbstract implements GoodMannersQuery
{
    protected $structure;
    protected $condition;
    
    protected $limitStart;
    protected $limitAmount;
    
    public function __construct($structure, $condition)
    {
        $this->structure = $structure;
        $this->condition = $condition;
    }
    
    public function limit($limitStart, $limitAmount)
    {
        $this->limitStart = $limitStart;
        $this->limitAmount = $limitAmount;
    }
}