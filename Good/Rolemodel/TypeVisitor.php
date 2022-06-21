<?php

namespace Good\Rolemodel;

//
// We are working on a structure that doesn't adhere too closely to the
// composite pattern.
// Nevertheless, we have a tree structure which we want to decouple from
// the functionality from the tree structure itself. The Visitor pattern
// does just that.
//

interface TypeVisitor
{
    public function visitReferenceType(Schema\Type\ReferenceType $type);
    public function visitTextType(Schema\Type\TextType $type);
    public function visitIntType(Schema\Type\IntType $type);
    public function visitFloatType(Schema\Type\FloatType $type);
    public function visitDateTimeType(Schema\Type\DatetimeType $type);
    public function visitBooleanType(Schema\Type\BooleanType $type);
    public function visitCollectionType(Schema\Type\CollectionType $type);
}

?>
