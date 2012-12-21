<?php

class GoodMannersStructure
{
    private $tables;
    private $id;
    
    public function __construct($tableName, $fieldNames)
    {
        $this->tables[] = array('name' => $tableName,
                                'fields' => $fieldNames);
        $id = null;
    }
    
    public addTable($tableName, $fieldNames, $joinOther, $joinThis)
    {
        // I should really check if joinOther is valid here...
        
        $this->tables[] = array('name' => $tableName,
                                'fields' => $fieldNames,
                                'joinOther' => $joinOther,
                                'joinThis' => $joinThis);
    }
    
    public function useAsIdentifier($id)
    {
        // once again, I should check for the existence of the fieldNames
        
        $this->id = $id;
    }
}