<?php


abstract class GoodMannersQueryAbstract implements GoodMannersQuery
{
	protected $db;
	
    protected $structure;
    protected $condition;
    
    protected $limitStart;
    protected $limitAmount;
    
    public function __construct(&$db, $structure, $condition)
    {
		$this->db = $db;
		
        $this->structure = $structure;
        $this->condition = $condition;
    }
    
    public function limit($limitStart, $limitAmount)
    {
        $this->limitStart = $limitStart;
        $this->limitAmount = $limitAmount;
    }
}