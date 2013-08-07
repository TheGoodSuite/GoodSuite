<?php

namespace Good\temptools;

use Good\Rolemodel\Schema;

// This is a temporary script that fixes the incompatibility between the
// GoodLooking way of accessing variables with the GoodMannersStorables
// Though this should be fixed on a higher level, this uses the temporary
// solution of creating a script that rewrites the GoodMannersStorables
// content into a format that works with GoodLooking

// input $model - GoodRoleModelDataModel
//
// task: writes a script "LookingWithManners.php", which alows your Storables
//       to be rewritten to arrays which GoodLooking can handle
//
//       not termin

// **Generated script**
//
// function parse<Type>($storable)
//   <Type>: Storable type
//   $storable: A GoodMannersStorable of type <Type> that should be rewritten
//              as an array.
//   returns: An array that contains all the information from $storable
//
// function parse<Type>Collection($collection)
//   <Type>: Storable type
//   $collection: A Collection of the Collection type for <Type>
//   returns: An array with all the Storables from $collection. The array will
//            non-sparse, thus beginning at 0 and sequentially numbered from
//            there. The order of the objects will be the same as in
//            $collection (thus allowing for proper usages of ordering on
//            the collection.
//
// NOTE: If a given Storable contains a circular reference, this script will
//       not terminate

class LookingWithMannersCompiler implements \Good\Rolemodel\SchemaVisitor
{
    private $outputDir;
    private $output = null;
    private $firstDataType = true;
    
    public function compile($model, $outputDir)
    {
        $this->outputDir = $outputDir;
        
        $model->acceptSchemaVisitor($this);
    }
    
    public function visitSchema(Schema $schema)
    {
        $this->output  = "<?php\n";
        $this->output .= "\n";
    }
    
    public function visitSchemaEnd()
    {
        if (!$this->firstDataType)
        {
            $this->finishDataType();
        }
        
        // close the file off
        $this->output .= "\n";
        $this->output .= "?>";
        
        \file_put_contents($this->outputDir . 'LookingWithManners.php', $this->output);
    }
    
    public function visitDataType(Schema\DataType $dataType)
    {
        if ($this->firstDataType)
        {
            $this->firstDataType = false;
        }
        else
        {
            $this->finishDataType();
        }
        
        $className = $dataType->getName();
        
        $this->output .= 'function parse' . \ucfirst($className) . 'Collection($collection)' . "\n";
        $this->output .= "{\n";
        $this->output .= '    $arr = array();' . "\n";
        $this->output .= "    \n";
        $this->output .= '    while ($obj = $collection->getNext())' . "\n";
        $this->output .= "    {\n";
        $this->output .= '        $arr[] = parse' . \ucfirst($className) . '($obj);' . "\n";
        $this->output .= "    }\n";
        $this->output .= "    \n";
        $this->output .= '    return $arr;' . "\n";
        $this->output .= "}\n";
        $this->output .= "\n";
        $this->output .= 'function parse' . \ucfirst($className) . '($obj)' . "\n";
        $this->output .= "{\n";
        $this->output .= '    if ($obj == null)' . "\n";
        $this->output .= "    {\n";
        $this->output .= '        return null;' . "\n";
        $this->output .= "    }\n";
        $this->output .= "    \n";
        $this->output .= '    $arr = array();' . "\n";
        $this->output .= "    \n";
        $this->output .= '    $arr["id"] = $obj->getId();' . "\n";
    }
    
    private function finishDataType()
    {
        $this->output .= "    \n";
        $this->output .= '    return $arr;' . "\n";
        $this->output .= "}\n";
        $this->output .= "\n";
    }
    
    public function referenceMember(Schema\ReferenceMember $member)
    {
        // This is a pretty ugly hack and it should perhaps be fixed
        // (move the visibility to the datamodel from the compiler)
        // but since this whole script is in fact an ugly fix,
        // I allowed it for now.
        if (!(\in_array('private', $dataMember->getAttributes()) || 
              \in_array('protected', $dataMember->getAttributes())))
        {
            $this->output .= '    $arr["' . $member->getName() . '"] = parse' . 
                                    \ucfirst($type->getReferencedType()) .
                                    '($obj->get' . ucfirst($member->getName()) . '());' . "\n";
        }
    }
    
    public function visitTextMember(Schema\TextMember $member)
    {
        $this->visitNonReference($member);
    }
    
    public function visitIntMember(Schema\IntMember $member)
    {
        $this->visitNonReference($member);
    }
    
    public function visitFloatMember(Schema\FloatMember $member)
    {
        $this->visitNonReference($member);
    }
    
    public function visitDatetimeMember($member)
    {
        // This is a pretty ugly hack and it should perhaps be fixed
        // (move the visibility to the datamodel from the compiler)
        // but since this whole script is in fact an ugly fix,
        // I allowed it for now.
        if (!(\in_array('private', $dataMember->getAttributes()) || 
              \in_array('protected', $dataMember->getAttributes())))
        {
            // It's a hack and should in the future be handled by GoodLooking
            // (where we can then also allow custom date formatting)
            $this->output .= '    $arr["' . $member->getName() . '"] = $obj->get' . 
                            \ucfirst($member->getName()) . '()->format("Y-m-d H:i:s");' . "\n";
        }
    }
    
    private function visitNonReference(Schema\PrimitiveMember $member)
    {
        // This is a pretty ugly hack and it should perhaps be fixed
        // (move the visibility to the datamodel from the compiler)
        // but since this whole script is in fact an ugly fix,
        // I allowed it for now.
        if (!(\in_array('private', $dataMember->getAttributes()) || 
              \in_array('protected', $dataMember->getAttributes())))
        {
            $this->output .= '    $arr["' . $member->getName() . '"] = $obj->get' . 
                                                \ucfirst($member->getName()) . '();' . "\n";
        }
    }
}