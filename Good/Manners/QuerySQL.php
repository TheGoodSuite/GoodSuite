<?php

class GoodMannersQuerySQL implements GoodMannersQuery
{
    private $sql;
    
    public function __construct($sql)
    {
        $this->sql = $sql;
    }
    
    public function getSQL()
    {
        return $this->sql;
    }
}

?>