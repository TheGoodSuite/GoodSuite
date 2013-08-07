<?php

namespace Good\Rolemodel\Schema;

use Good\Rolemodel\SchemaVisitor;

class ReferenceMember extends Member
{
    private $referencedType;
    
    public function __construct(array $attributes, $name, $referencedType)
    {
        parent::__construct($attributes, $name);
        
        $this->referencedType = $referencedType;
    }
    
    public function acceptSchemaVisitor(SchemaVisitor $visitor)
    {
        // visit this, there are no children to pass visitor on to
        $visitor->visitReferenceMember($this);
    }
    
    public function getReferencedType()
    {
        return $this->referencedType;
    }
    
    public function getReferencedTypeIfAny()
    {
        return $this->referencedType;
    }
}

?>