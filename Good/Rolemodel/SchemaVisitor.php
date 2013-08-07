<?php

namespace Good\Rolemodel;

//
// We are working on a structure that doesn't adhere too closely to the
// composite pattern.
// Nevertheless, we have a tree structure which we want to decouple from 
// the functionality from the tree structure itself. The Visitor pattern
// does just that.
// 

interface SchemaVisitor
{
    public function visitSchema(Schema $dataModel);
    public function visitSchemaEnd();
    public function visitDataType(Schema\DataType $dataType);
    public function visitReferenceMember(Schema\ReferenceMember $type);
    public function visitTextMember(Schema\TextMember $type);
    public function visitIntMember(Schema\IntMember $type);
    public function visitFloatMember(Schema\FloatMember $type);
    public function visitDatetimeMember(Schema\DatetimeMember $type);
}

?>