<?php

namespace Good\Rolemodel\Schema;

use Good\Rolemodel\InvalidTypeModifierException;

abstract class PrimitiveMember extends Member
{
    private $typeModifiers;

    abstract function getValidParameterTypeModifiers();
    abstract function getValidNonParameterTypeModifiers();
    abstract function processTypeModifiers(array $typeModifiers);
    abstract function getDefaultTypeModifierValues();

    public function __construct(array $attributes, $name, array $typeModifiers)
    {
        parent::__construct($attributes, $name);

        $this->validateTypeModifiers($typeModifiers);
        $typeModifiers = $this->processTypeModifiers($typeModifiers);
        $typeModifiers = array_merge($this->getDefaultTypeModifierValues(), $typeModifiers);

        $this->typeModifiers = $typeModifiers;
    }

    public function getReferencedTypeIfAny()
    {
        return null;
    }

    public function getTypeModifiers()
    {
        return $this->typeModifiers;
    }

    private function validateTypeModifiers(array $typeModifiers)
    {
        // Turn them into "sets" that have O(1) lookup
        $allowedParameterModifiers = array_flip($this->getValidParameterTypeModifiers());
        $allowedNonParameterModifiers = array_flip($this->getValidNonParameterTypeModifiers());

        foreach ($typeModifiers as $modifier => $value)
        {
            if ($value === true) // strict check!
            {
                if (!array_key_exists($modifier, $allowedNonParameterModifiers))
                {
                    if (array_key_exists($modifier, $allowedParameterModifiers))
                    {
                        throw new InvalidTypeModifierException('Type modifier "' . $modifier . '" on ' . $this->getName() . ' must have a value.');
                    }
                    else
                    {
                        throw new InvalidTypeModifierException('Unknown type modifier "' . $modifier . '" on ' . $this->getName() . '.');
                    }
                }
            }
            else
            {
                if (!array_key_exists($modifier, $allowedParameterModifiers))
                {
                    if (array_key_exists($modifier, $allowedNonParameterModifiers))
                    {
                        throw new InvalidTypeModifierException('Type modifier "' . $modifier . '" on ' . $this->getName() . ' must not have a value.');
                    }
                    else
                    {
                        throw new InvalidTypeModifierException('Unknown type modifier "' . $modifier . '" on ' . $this->getName() . '.');
                    }
                }
            }
        }
    }
}

?>
