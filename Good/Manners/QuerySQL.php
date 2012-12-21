<?php

class GoodMannersQuerySQL implements GoodMannersQuery
{
    private $sql;
    
    public function __construct(&$db, $sql)
    {
		$this->db = $db;
		
        $this->sql = $sql;
    }
    
    public function execute()
    {
        return $this->sql;
    }
}

?>