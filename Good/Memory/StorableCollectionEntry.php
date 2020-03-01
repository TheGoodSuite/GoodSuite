<?php

namespace Good\Memory;

use Good\Manners\Storable;
use Good\Manners\StorableVisitor;
use Good\Manners\Storage;
use Good\Manners\ValidationToken;
use Good\Rolemodel\Schema\Type;
use Good\Rolemodel\Schema\Type\CollectionType;
use Good\Rolemodel\Schema\Type\DateTimeType;
use Good\Rolemodel\Schema\Type\FloatType;
use Good\Rolemodel\Schema\Type\IntType;
use Good\Rolemodel\Schema\Type\ReferenceType;
use Good\Rolemodel\Schema\Type\TextType;
use Good\Rolemodel\TypeVisitor;

class StorableCollectionEntry implements Storable, TypeVisitor
{
    public function delete() {}
    public function isDeleted() {}
    public function setNew($value) {}
    public function isNew() {}
    public function setStorage(Storage $storage) {}
    public function setValidationToken(ValidationToken $token) {}
    public function getId() {}
    public function setId($value) {}
    public function hasValidId() {}
    public function isDirty() {}
    public function clean() {}
    public function getType() {}
    public function markCollectionsUnresolved() {}

    private $storableVisitor;

    public function acceptStorableVisitor(StorableVisitor $visitor)
    {
        $visitor->visitReferenceProperty("owner", $this->owner->getType(), true, $this->owner);

        $this->storableVisitor = $visitor;
        $this->type->acceptTypeVisitor($this);
    }

    public function visitCollectionType(CollectionType $type)
    {
        throw new \Exception("Collections of Collection are not supported at the moment.");
    }

    public function  visitDateTimeType(DateTimeType $type)
    {
        $this->storableVisitor->visitDateTimeProperty("value", true, $this->value);
    }

    public function visitFloatType(FloatType $type)
    {
        $this->storableVisitor->visitFloatProperty("value", true, $this->value);
    }

    public function visitIntType(IntType $type)
    {
        $this->storableVisitor->visitIntProperty("value", true, $this->value);
    }

    public function visitReferenceType(ReferenceType $type)
    {
        $this->storableVisitor->visitReferenceProperty("value", $type->getReferencedType(), true, $this->value);
    }

    public function visitTextType(TextType $type)
    {
        $this->storableVisitor->visitTextProperty("value", true, $this->value);
    }

    private $owner;
    private $value;
    private $collectionFieldName;
    private $type;

    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    public function setOwner(Storable $owner)
    {
        $this->owner = $owner;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function setCollectionFieldName($name)
    {
        $this->collectionFieldName = $name;
    }

    public function getCollectionFieldName()
    {
        return $this->collectionFieldName;
    }
}

?>
