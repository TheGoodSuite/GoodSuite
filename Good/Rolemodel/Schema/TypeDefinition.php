<?php

namespace Good\Rolemodel\Schema;

use Good\Rolemodel\SchemaVisitor;

class TypeDefinition
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

    public function getMembers()
    {
        return $this->members;
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
            $newElement = $member->getType()->getReferencedTypeIfAny();

            if ($newElement != null)
            {
                $res[] = $newElement;
            }
        }

        return $res;
    }
}

?>
