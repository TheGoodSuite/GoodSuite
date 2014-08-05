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
        
        foreach ($dataTypeArrays as $dataTypeArray)
        {
            foreach ($dataTypeArray as $dataType)
            {
                $dataTypes[] = $dataType;
            }
        }
        
        return new Schema($dataTypes);
    }
    
    private function fileToDataTypes($file)
    {
        // read the file
        $input = \file_get_contents($file);
        
        // Cutting out php tags out if they are there
        // (they are allowed as an additional method to make the content unaccesible)
        
        if (substr($input, 0, 5) == '<?php')
        {
            $input = \substr($input, 5);
        }
        
        if (substr($input, -2) == '?>')
        {
            $input = \substr($input, 0, -2);
        }
        
        // building a complicated regex just once
        // (so outside the for loop)
        $identifier = '[a-zA-Z_][a-zA-Z0-9_]*';
        $regexAttributeSeperator = '(\\s*,\\s*|\\s+)';
        $regexAttributes = '\\[\\s*(?P<attributes>[a-zA-Z0-9_]+(' . $regexAttributeSeperator . '[a-zA-Z0-9_]+)*\\s*)?\\]';
        $regexType = '(?P<type>' . $identifier . '|"' . $identifier . '")';
        $regexName = '(?P<name>' . $identifier . ')';
        $memberFinisher = ';';
        $memberDefinition = '\\s*(' . $regexAttributes . '\\s*)?' . $regexType . '\\s+' . $regexName . '\\s*' . $memberFinisher;
        
        $datatypeBegin = '\\s*datatype\\s+(?<datatypeName>' . $identifier . ')\s*{';
        $dataTypeEnd = '\\s*}';
        
        $factory = new PrimitiveFactory();
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
                
                $type = $matches['type'];
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
                if (\substr($type, 0, 1) == '"' && \substr($type, -1) == '"')
                {
                    $members[] = new Schema\ReferenceMember($attributes, $varName, \substr($type, 1, -1));
                }
                else
                {
                    $members[] = $factory->makePrimitive($attributes, $varName, $type);
                }
            }
            
            if (preg_match('/^' . $dataTypeEnd . '\s*/', $input, $matches) !== 1)
            {
                // TODO: better error handling outputting, et al
                throw new \Exception("Malformed Datamodel file: " . $file);
            }
            
            $input = substr($input, strlen($matches[0]));
            
            $types[] = new Schema\DataType($file, $datatypeName, $members);
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