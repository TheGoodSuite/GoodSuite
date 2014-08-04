<?php

namespace Good\Rolemodel;

class Rolemodel
{
    public function createSchema(array $input)
    {
        $dataTypes = array();
        
        foreach ($input as $name => $file)
        {
            $dataTypes[] = $this->fileToDataType($name, $file);
        }
        
        return new Schema($dataTypes);
    }
    
    private function fileToDataType($name, $file)
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
        $regexAttributeSeperator = '(\\s*,\\s*|\\s+)';
        $regexAttributes = '\\[(?P<attributes>[a-zA-Z0-9_]+(' . $regexAttributeSeperator . '[a-zA-Z0-9_]+)*\\s*)?\\]';
        $regexType = '(?P<type>([a-zA-Z_][a-zA-Z0-9_]*|"[a-zA-Z_][a-zA-Z0-9_]*"))';
        $regexName = '(?P<name>[a-zA-Z_][a-zA-Z0-9_]*)';
        $memberFinisher = ';';
        $memberDefinition = '\\s*(' . $regexAttributes . '\\s*)?' . $regexType . '\\s+' . $regexName . '\\s*' . $memberFinisher;
        
        // We'll use this array to build the result in
        $members = array();
        
        $factory = new PrimitiveFactory();
        
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
        
        if (preg_match("/^\s*$/", $input) !== 1)
        {
            // TODO: better error handling outputting, et al
            throw new \Exception("Malformed Datamodel file: " . $file);
        }
        
        return new Schema\DataType($file, $name, $members);
    }
}

?>