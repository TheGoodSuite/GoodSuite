<?php

namespace Good\Rolemodel\Schema;

use Good\Rolemodel\SchemaVisitor;

class DataType
{
    private $sourceFileName;
    private $name;
    private $members;
    
    public function __construct($sourceFileName, $name, array $members)
    {
        $this->sourceFileName = $sourceFileName;
        // TODO: make sure name is valid
        $this->name = $name;
        $this->members = $members;
    }
    
    public function acceptSchemaVisitor(SchemaVisitor $visitor)
    {
        // visit this
        $visitor->visitDataType($this);
        
        // and move the visitor to your children
        foreach ($this->members as $member)
        {
            $member->acceptSchemaVisitor($visitor);
        }
    }
    
    public function getSourceFileName()
    {
        return $this->sourceFileName;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getReferencedTypes()
    {
        $res = array();
        
        foreach ($this->members as $member)
        {
            $newElement = $member->getReferencedTypeIfAny();
            
            if ($newElement != null)
            {
                $res[] = $newElement;
            }
        }
        
        return $res;
    }
}

?>