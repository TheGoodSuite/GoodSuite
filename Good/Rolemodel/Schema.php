<?php

namespace Good\Rolemodel;

class Schema
{
    private $typeDefinitions;

    public function __construct(array $typeDefinitions)
    {
        // we index the types by their names, so we can easily access them
        // hm... I really need to take a look at this.
        // I don't really see a good reason to be doing this, actually.
        $this->typeDefinitions = array();
        foreach ($typeDefinitions as $typeDefinition)
        {
            $this->typeDefinitions[$typeDefinition->getName()] = $typeDefinition;
        }

        // Make sure all referenced dataTypes are present
        foreach ($this->typeDefinitions as $typeDefinition)
        {
            $references = $typeDefinition->getReferencedTypes();

            foreach ($references as $reference)
            {
                if (!isset($this->typeDefinitions[$reference]))
                {
                    // TODO: better error handling

                    throw new \Exception("Error: Type " . $reference . " was referenced, but not supplied itself.");
                }
            }
        }
    }

    public function getTypeDefitions()
    {
        return $this->typeDefinitions;
    }
}

?>
