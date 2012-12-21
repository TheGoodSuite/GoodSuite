<?php

class GoodManners
{
    $db;
    
    public function __construct()
    {
        // pick right database class, store new in $this->db
    }
    
    public function query($query)
    {
        $this->db->query($query);
        
        new GoodMannersResult($db->getIdentifier());
        // or something like that (or not like it at all...)
        
        return $res;
    }
}