<?php

namespace Good\Rolemodel;

class Rolemodel
{
    public function createSchema(array $input)
    {
        // use an intermediate array to prevent unnecessary O-factor increase
        $dataTypeArrays = array();

        foreach ($input as $file)
        {
            $dataTypeArrays[] = $this->fileToDataTypes($file);
        }

        $dataTypes = array();
        $dataTypeNames = [];

        foreach ($dataTypeArrays as $dataTypeArray)
        {
            foreach ($dataTypeArray as $dataType)
            {
                $dataTypes[] = $dataType;

                if (array_key_exists($dataType->getName(), $dataTypeNames))
                {
                    throw new \Exception('More than datatype found with name "' . $dataType->getName() . '"');
                }

                $dataTypeNames[$dataType->getName()] = true;
            }
        }

        return new Schema($dataTypes);
    }

    private function fileToDataTypes($file)
    {
        // read the file
        $input = \file_get_contents($file);

        // building a complicated regex just once
        // (so outside the for loop)
        $identifier = '[a-zA-Z_][a-zA-Z0-9_]*';
        $regexAttributeSeperator = '(\\s*,\\s*|\\s+)';
        $regexAttributes = '\\[\\s*(?P<attributes>[a-zA-Z0-9_]+(' . $regexAttributeSeperator . '[a-zA-Z0-9_]+)*\\s*)?\\]';
        $regexTypeModifier = '(?P<typeModifier>(?P<typeModifierName>[a-zA-Z][a-zA-Z0-9_]*)\s*(?:=\s*(?P<typeModifierValue>-?[1-9][0-9]*))?)';
        // some complexity was added through nextTypeModifier, which is there to ensure that firstSeparator captures the first separator instead of the last
        // (without extra backtracking)
        $regexTypeModifiers = '(?P<firstTypeModifierPart>\\s*' . $regexTypeModifier . '\\s*)(?:(?P<nextTypeModifier>(?P<firstSeparator>,)\\s*(?P>typeModifier)\\s*)(?P>nextTypeModifier)*)?';
        $regexBaseType = '(?:(?P<primitiveType>' . $identifier . ')(?:\\((?:(?<typeModifiers>' . $regexTypeModifiers . ')|\\s*)\\))?|"(?P<referenceType>' . $identifier . ')")';
        $regexType = '(?:' . $regexBaseType . '(?P<collection>\[\])?)';
        $regexName = '(?P<name>' . $identifier . ')';
        $memberFinisher = ';';
        $memberDefinition = '\\s*(' . $regexAttributes . '\\s*)?' . $regexType . '\\s+' . $regexName . '\\s*' . $memberFinisher;

        $datatypeBegin = '\\s*datatype\\s+(?<datatypeName>' . $identifier . ')\s*{';
        $dataTypeEnd = '\\s*}';

        $factory = new PrimitiveTypeFactory();
        $types = array();

        while (preg_match('/^' . $datatypeBegin . '/', $input, $matches) === 1)
        {
            // We'll use this array to build the result in
            $members = array();

            $input = substr($input, strlen($matches[0]));
            $datatypeName = $matches['datatypeName'];

            while (preg_match('/^' . $memberDefinition . '/', $input, $matches) === 1)
            {
                $line = $matches[0];
                $input = substr($input, strlen($line));

                $varName = $matches['name'];
                if ($matches['attributes'] != '')
                {
                    $attributes = \preg_split('/' . $regexAttributeSeperator . '/', $matches['attributes']);
                }
                else
                {
                    $attributes = array();
                }

                // Type
                if (array_key_exists('referenceType', $matches) && $matches['referenceType'] !== "")
                {
                    $type = new Schema\Type\ReferenceType($matches['referenceType']);
                }
                else
                {
                    // extract typeModifiers
                    $typeModifiers = array();

                    if (array_key_exists('typeModifiers', $matches) && $matches['typeModifiers'] !== "")
                    {
                        $typeModifierSource = $matches['typeModifiers'];

                        while (preg_match('/^' . $regexTypeModifiers . '$/', $typeModifierSource, $typeModifierMatches) != 0 && $typeModifierMatches[0] != "")
                        {
                            $typeModifierName = $typeModifierMatches['typeModifierName'];

                            if (array_key_exists($typeModifierName, $typeModifiers))
                            {
                                throw new \Exception("Same type modifier found more than once on a single property");
                            }

                            if (array_key_exists('typeModifierValue', $typeModifierMatches) && $typeModifierMatches['typeModifierValue'] !== "")
                            {
                                $typeModifiers[$typeModifierName] = intval($typeModifierMatches['typeModifierValue']);
                            }
                            else
                            {
                                $typeModifiers[$typeModifierName] = true;
                            }

                            $cutOff = strlen($typeModifierMatches['firstTypeModifierPart']);

                            if (array_key_exists('firstSeparator', $typeModifierMatches) && $typeModifierMatches['firstSeparator'] !== "")
                            {
                                $cutOff += strlen($typeModifierMatches['firstSeparator']);
                            }

                            $typeModifierSource = \substr($typeModifierSource, $cutOff);
                        }
                    }

                    $type = $factory->makePrimitiveType($matches['primitiveType'], $typeModifiers, $varName);
                }

                if (array_key_exists('collection', $matches) && $matches['collection'] !== "")
                {
                    $type = new Schema\Type\CollectionType($type);
                }

                $members[] = new Schema\Member($attributes, $varName, $type);
            }

            if (preg_match('/^' . $dataTypeEnd . '\s*/', $input, $matches) !== 1)
            {
                // TODO: better error handling outputting, et al
                throw new \Exception("Malformed Datamodel file: " . $file);
            }

            $input = substr($input, strlen($matches[0]));

            $types[] = new Schema\TypeDefinition($file, $datatypeName, $members);
        }

        if (preg_match("/^\s*$/", $input) !== 1)
        {
            // TODO: better error handling outputting, et al
            throw new \Exception("Malformed Datamodel file: " . $file);
        }

        return $types;
    }
}

?>
